<?php
// cette page prend en entrÃ©e un code d'invitation 'invit' et le username invitÃ© 'user' associÃ©
if (!empty($_SESSION['login_user'])) { //la session est bonne on redirige vers page membre
    include_once(__DIR__ . '/../config.php');
}
else {
    session_start();
    include_once(__DIR__ . '/../config.php');
}
include_once(__DIR__."/../fonctions.inc.php"); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Synchronisation Flash Invaders</title>
    <style>
        .step {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .hidden {
            display: none;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
            font-weight: bold;
        }
        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 10px;
        }
        button:disabled {
            background-color: #cccccc;
        }
        .uid-container {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        input {
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            color: rgb(224, 224, 224);
        }
        input[readonly] {
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            color:  #757575;
        }
        .edit-controls {
            display: flex;
            gap: 10px;
        }
        #editButton {
            background-color: #f0ad4e;
        }
        #clearButton {
            background-color: #d9534f;
        }

    </style>

</head>
<body>
    <div class="container">
        <h3>Synchronisez vos Flash !</h3>
        
        <div class="step" id="step1">
            <h4>Étape 1: Votre UID Flash Invaders</h4>
            <p>
              <a href="modules/notice_uid.html" target="_blank" style="text-decoration: underline; color: #007bff;">
                Consultez la notice pour savoir comment récupérer votre UID.
              </a>
            </p>
            <div class="uid-container">
                <input type="text" id="uidInput" placeholder="Votre UID..." style="width: 300px; padding: 5px;">
                <div class="edit-controls">
                    <button id="verifyButton" onclick="verifyUID()">Vérifier</button>
                    <button id="editButton" class="hidden" onclick="enableEdit()">Modifier</button>
                    <button id="clearButton" class="hidden" onclick="clearUID()">Effacer</button>
                </div>
            </div>
            <div id="uidResult"></div>
        </div>

        <div class="step hidden" id="step2">
            <h4>Étape 2: Synchronisation</h4>
            <p>Cliquez sur le bouton ci-dessous pour récupérer vos données Flash Invaders</p>
            <button onclick="getFlashData()">Récupérer mes données</button>
            <div id="dataResult" class="hidden">
                <div id="dataPreview" style="margin: 20px 0; padding: 15px; border-radius: 5px; background: #000; opacity:0.7;"></div>
                <button onclick="synchronizeFlashes()" id="confirmSync">Confirmer la synchronisation</button>
                <button onclick="cancelSync()" style="background-color: #6c757d;">Annuler</button>
            </div>
            <div id="syncResult"></div>
        </div>
    </div>

    <script>
        // Au chargement de la page, on vérifie si un UID existe déjà
        window.addEventListener('load', async () => {
            try {
                const response = await fetch('modules/get_user_uid.php');
                const data = await response.json();
                
                if (data.uid_flashinvader) {
                    const uidInput = document.getElementById('uidInput');
                    uidInput.value = data.uid_flashinvader;
                    uidInput.readOnly = true;
                    
                    document.getElementById('verifyButton').classList.add('hidden');
                    document.getElementById('editButton').classList.remove('hidden');
                    document.getElementById('clearButton').classList.remove('hidden');
                    
                    // On vérifie automatiquement l'UID existant
                    await verifyUID();
                }
            } catch (error) {
                console.error('Erreur lors de la récupération de l\'UID:', error);
            }
        });

        function enableEdit() {
            const uidInput = document.getElementById('uidInput');
            uidInput.readOnly = false;
            uidInput.focus();
            
            document.getElementById('verifyButton').classList.remove('hidden');
            document.getElementById('editButton').classList.add('hidden');
            document.getElementById('clearButton').classList.add('hidden');
            
            document.getElementById('step2').classList.add('hidden');
            document.getElementById('uidResult').innerHTML = '';
        }

        function clearUID() {
            const uidInput = document.getElementById('uidInput');
            uidInput.value = '';
            uidInput.readOnly = false;
            
            document.getElementById('verifyButton').classList.remove('hidden');
            document.getElementById('editButton').classList.add('hidden');
            document.getElementById('clearButton').classList.add('hidden');
            
            document.getElementById('step2').classList.add('hidden');
            document.getElementById('uidResult').innerHTML = '';
            
            // On efface aussi en base
            fetch('modules/clear_user_uid.php', {
                method: 'POST'
            }).catch(error => console.error('Erreur lors de l\'effacement de l\'UID:', error));
        }

        async function verifyUID() {
            const uid = document.getElementById('uidInput').value.trim();
            const resultDiv = document.getElementById('uidResult');
            
            try {
                const response = await fetch(`https://api.space-invaders.com/flashinvaders_v3_pas_trop_predictif/api/account?uid=${uid}`);
                const data = await response.json();
                
                if (data.code === 0) {
                    // Sauvegarde en base de données via notre API
                    const saveResponse = await fetch('modules/ajax_update_user_synchro.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            uid_flashinvader: uid,
                            game_name: data.name
                        })
                    });

                    if (saveResponse.ok) {
                        resultDiv.innerHTML = `<p class="success">✓ UID vérifié pour le joueur: ${data.name}</p>`;
                        document.getElementById('step2').classList.remove('hidden');
                        
                        // On met l'input en readonly et on affiche les boutons de modification
                        const uidInput = document.getElementById('uidInput');
                        uidInput.readOnly = true;
                        document.getElementById('verifyButton').classList.add('hidden');
                        document.getElementById('editButton').classList.remove('hidden');
                        document.getElementById('clearButton').classList.remove('hidden');
                    } else {
                        resultDiv.innerHTML = '<p class="error">Erreur lors de la sauvegarde des données</p>';
                    }
                } else {
                    resultDiv.innerHTML = '<p class="error">UID invalide</p>';
                }
            } catch (error) {
                resultDiv.innerHTML = '<p class="error">Erreur lors de la vérification</p>';
                console.error('Erreur:', error);
            }
        }

        async function getFlashData() {
            const uid = document.getElementById('uidInput').value.trim();
            const dataPreview = document.getElementById('dataPreview');
            const dataResult = document.getElementById('dataResult');
            const syncResult = document.getElementById('syncResult');
            
            try {
                const response = await fetch(`https://api.space-invaders.com/flashinvaders_v3_pas_trop_predictif/api/gallery?uid=${uid}`);
                const data = await response.json();
                
                if (data.code === 0) {
                    // Stockage temporaire des données
                    window.flashData = data;
                    
                    // Calcul des statistiques
                    const invaderCount = Object.keys(data.invaders).length;
                    const totalScore = data.player.score;
                    const cityCount = data.player.city_found;
                    
                    // Affichage du résumé
                    dataPreview.innerHTML = `
                        <h4>Résumé de vos données</h4>
                        <ul>
                            <li>Nombre d'invaders flashés : <strong>${invaderCount}</strong></li>
                            <li>Score total : <strong>${totalScore}</strong></li>
                            <li>Nombre de villes visitées : <strong>${cityCount}</strong></li>
                        </ul>
                        <h5>Voulez-vous synchroniser ces données avec votre compte ?</h5>
                    `;
                    
                    dataResult.classList.remove('hidden');
                    syncResult.innerHTML = '';
                } else {
                    syncResult.innerHTML = '<p class="error">Erreur lors de la récupération des données</p>';
                }
            } catch (error) {
                syncResult.innerHTML = '<p class="error">Erreur lors de la récupération des données</p>';
                console.error('Erreur:', error);
            }
        }

        function cancelSync() {
            const dataResult = document.getElementById('dataResult');
            const syncResult = document.getElementById('syncResult');
            
            dataResult.classList.add('hidden');
            syncResult.innerHTML = '';
            window.flashData = null;
        }

        async function synchronizeFlashes() {
            if (!window.flashData) {
                return;
            }

            const syncResult = document.getElementById('syncResult');
            const dataResult = document.getElementById('dataResult');
            
            try {
                // Envoi des données à notre API
                const saveResponse = await fetch('modules/ajax_update_flash_with_synchro.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(window.flashData)
                });

                if (saveResponse.ok) {
                    syncResult.innerHTML = `<p class="success">✓ Synchronisation réussie! ${Object.keys(window.flashData.invaders).length} invaders synchronisés.</p>`;
                    dataResult.classList.add('hidden');
                    window.flashData = null;
                } else {
                    const errorData = await saveResponse.json();
                    syncResult.innerHTML = `<p class="error">Erreur lors de la synchronisation: ${errorData.message || 'Erreur inconnue'}</p>`;
                }
            } catch (error) {
                syncResult.innerHTML = '<p class="error">Erreur lors de la synchronisation</p>';
                console.error('Erreur:', error);
            }
        }
    </script>


</body>
</html>