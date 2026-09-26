// js/fuzzy-text.js

class FuzzyText {
  constructor(canvasElement, options = {}) {
    this.canvas = canvasElement;
    this.options = {
      text: options.text || '',
      fontSize: options.fontSize || "clamp(2rem, 10vw, 10rem)",
      fontWeight: options.fontWeight || 900,
      fontFamily: options.fontFamily || "inherit",
      color: options.color || "#fff",
      enableHover: options.enableHover !== false,
      baseIntensity: options.baseIntensity || 0.18,
      hoverIntensity: options.hoverIntensity || 0.5,
      // NOUVELLES OPTIONS : Nombre d'images à pré-calculer
      numFrames: options.numFrames || 15,
      numHoverFrames: options.numHoverFrames || 20,
    };
    
    this.animationFrameId = null;
    this.isCancelled = false;
    this.isHovering = false;
    
    // Tableaux pour stocker les frames pré-calculées
    this.baseFrames = [];
    this.hoverFrames = [];
    this.frameIndex = 0;
    
    // On s'assure que `this` est correct dans la boucle d'animation
    this.loop = this.loop.bind(this);
    
    this.init();
  }

  async init() {
    if (document.fonts?.ready) {
      await document.fonts.ready;
    }
    if (this.isCancelled) return;

    const ctx = this.canvas.getContext("2d");
    if (!ctx) return;

    // --- Toute la partie calcul des dimensions reste identique ---
    const computedFontFamily = this.options.fontFamily === "inherit"
      ? window.getComputedStyle(this.canvas).fontFamily || "sans-serif"
      : this.options.fontFamily;

    const fontSizeStr = typeof this.options.fontSize === "number" 
      ? `${this.options.fontSize}px` 
      : this.options.fontSize;
      
    let numericFontSize;
    if (typeof this.options.fontSize === "number") {
      numericFontSize = this.options.fontSize;
    } else {
      const temp = document.createElement("span");
      temp.style.fontSize = this.options.fontSize;
      document.body.appendChild(temp);
      const computedSize = window.getComputedStyle(temp).fontSize;
      numericFontSize = parseFloat(computedSize);
      document.body.removeChild(temp);
    }

    const text = this.options.text;
    const offscreenBase = document.createElement("canvas");
    const offCtx = offscreenBase.getContext("2d");
    if (!offCtx) return;

    offCtx.font = `${this.options.fontWeight} ${fontSizeStr} ${computedFontFamily}`;
    offCtx.textBaseline = "alphabetic";
    const metrics = offCtx.measureText(text);

    const actualLeft = metrics.actualBoundingBoxLeft ?? 0;
    const actualRight = metrics.actualBoundingBoxRight ?? metrics.width;
    const actualAscent = metrics.actualBoundingBoxAscent ?? numericFontSize;
    const actualDescent = metrics.actualBoundingBoxDescent ?? numericFontSize * 0.2;

    const textBoundingWidth = Math.ceil(actualLeft + actualRight);
    const tightHeight = Math.ceil(actualAscent + actualDescent);

    const extraWidthBuffer = 10;
    const offscreenWidth = textBoundingWidth + extraWidthBuffer;

    offscreenBase.width = offscreenWidth;
    offscreenBase.height = tightHeight;

    const xOffset = extraWidthBuffer / 2;
    offCtx.font = `${this.options.fontWeight} ${fontSizeStr} ${computedFontFamily}`;
    offCtx.textBaseline = "alphabetic";
    offCtx.fillStyle = this.options.color;
    offCtx.fillText(text, xOffset - actualLeft, actualAscent);

    const horizontalMargin = 50;
    const verticalMargin = 0;
    this.canvas.width = offscreenWidth + horizontalMargin * 2;
    this.canvas.height = tightHeight + verticalMargin * 2;
    ctx.translate(horizontalMargin, verticalMargin);
    
    // --- NOUVEAU : Pré-calcul des frames ---
    const fuzzRange = 30;
    this.baseFrames = this._preRenderFrames(offscreenBase, this.options.baseIntensity, this.options.numFrames, fuzzRange, offscreenWidth, tightHeight);
    this.hoverFrames = this._preRenderFrames(offscreenBase, this.options.hoverIntensity, this.options.numHoverFrames, fuzzRange, offscreenWidth, tightHeight);

    // --- Démarrage de la boucle d'animation LÉGÈRE ---
    this.loop();

    // --- La gestion des évènements reste identique ---
    const interactiveLeft = horizontalMargin + xOffset;
    const interactiveTop = verticalMargin;
    const interactiveRight = interactiveLeft + textBoundingWidth;
    const interactiveBottom = interactiveTop + tightHeight;

    const isInsideTextArea = (x, y) => (x >= interactiveLeft && x <= interactiveRight && y >= interactiveTop && y <= interactiveBottom);

    const handleMouseMove = (e) => {
      if (!this.options.enableHover) return;
      const rect = this.canvas.getBoundingClientRect();
      this.isHovering = isInsideTextArea(e.clientX - rect.left, e.clientY - rect.top);
    };
    const handleMouseLeave = () => { this.isHovering = false; };
    const handleTouchMove = (e) => {
      if (!this.options.enableHover) return;
      e.preventDefault();
      const rect = this.canvas.getBoundingClientRect();
      const touch = e.touches[0];
      this.isHovering = isInsideTextArea(touch.clientX - rect.left, touch.clientY - rect.top);
    };
    const handleTouchEnd = () => { this.isHovering = false; };

    if (this.options.enableHover) {
      this.canvas.addEventListener("mousemove", handleMouseMove);
      this.canvas.addEventListener("mouseleave", handleMouseLeave);
      this.canvas.addEventListener("touchmove", handleTouchMove, { passive: false });
      this.canvas.addEventListener("touchend", handleTouchEnd);
    }

    this.cleanup = () => {
      window.cancelAnimationFrame(this.animationFrameId);
      if (this.options.enableHover) {
        this.canvas.removeEventListener("mousemove", handleMouseMove);
        this.canvas.removeEventListener("mouseleave", handleMouseLeave);
        this.canvas.removeEventListener("touchmove", handleTouchMove);
        this.canvas.removeEventListener("touchend", handleTouchEnd);
      }
    };
  }

  /**
   * NOUVELLE FONCTION : Génère un tableau de canvas pré-calculés.
   */
  _preRenderFrames(sourceCanvas, intensity, numFrames, fuzzRange, width, height) {
    const frames = [];
    for (let i = 0; i < numFrames; i++) {
      const frameCanvas = document.createElement("canvas");
      frameCanvas.width = width;
      frameCanvas.height = height;
      const frameCtx = frameCanvas.getContext("2d");
      
      // On applique l'effet de "fuzz" une seule fois par frame
      for (let j = 0; j < height; j++) {
        const dx = Math.floor(intensity * (Math.random() - 0.5) * fuzzRange);
        frameCtx.drawImage(sourceCanvas, 0, j, width, 1, dx, j, width, 1);
      }
      frames.push(frameCanvas);
    }
    return frames;
  }

  /**
   * NOUVELLE BOUCLE D'ANIMATION : Affiche juste la frame suivante.
   */
  loop() {
    if (this.isCancelled) return;

    const activeFrames = this.isHovering ? this.hoverFrames : this.baseFrames;
    if (activeFrames.length > 0) {
        const frameToDraw = activeFrames[this.frameIndex % activeFrames.length];
        
        const ctx = this.canvas.getContext("2d");
        ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        ctx.drawImage(frameToDraw, 0, 0);

        this.frameIndex++;
    }
    
    this.animationFrameId = window.requestAnimationFrame(this.loop);
  }

  destroy() {
    this.isCancelled = true;
    window.cancelAnimationFrame(this.animationFrameId);
    if (this.cleanup) {
      this.cleanup();
    }
  }
}

// La fonction utilitaire ne change pas
function createFuzzyText(canvasId, text, options = {}) {
  const canvas = document.getElementById(canvasId);
  if (!canvas) {
    console.error(`Canvas with id "${canvasId}" not found`);
    return null;
  }
  
  return new FuzzyText(canvas, { ...options, text });
}

window.FuzzyText = FuzzyText;
window.createFuzzyText = createFuzzyText;