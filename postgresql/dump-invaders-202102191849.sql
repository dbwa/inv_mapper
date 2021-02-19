--
-- PostgreSQL database dump
--

-- Dumped from database version 11.10 (Raspbian 11.10-0+deb10u1)
-- Dumped by pg_dump version 12.2

-- Started on 2021-02-19 18:49:31

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 3 (class 2615 OID 2200)
-- Name: public; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA public;


ALTER SCHEMA public OWNER TO postgres;

--
-- TOC entry 2933 (class 0 OID 0)
-- Dependencies: 3
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: postgres
--

COMMENT ON SCHEMA public IS 'standard public schema';


SET default_tablespace = '';

--
-- TOC entry 196 (class 1259 OID 24577)
-- Name: etat; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.etat (
    idx integer,
    inv_name character varying(1024),
    points character varying(1024),
    etat character varying(1024),
    last_maj character varying(1024),
    image1 character varying(1024),
    image2 character varying(1024),
    image3 character varying(1024)
);


ALTER TABLE public.etat OWNER TO postgres;

--
-- TOC entry 197 (class 1259 OID 24583)
-- Name: invit_users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.invit_users (
    username text,
    invitcode text,
    user_type text DEFAULT 'normal'::text,
    status text DEFAULT 'en attente'::text
);


ALTER TABLE public.invit_users OWNER TO postgres;

--
-- TOC entry 198 (class 1259 OID 24591)
-- Name: modif_state_user; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.modif_state_user (
    user_name text,
    inv_name text,
    etat text,
    date_modif date
);


ALTER TABLE public.modif_state_user OWNER TO postgres;

--
-- TOC entry 199 (class 1259 OID 24597)
-- Name: positions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.positions (
    inv_name character varying(1024),
    lat real,
    lon real,
    points integer,
    photo character varying(1024)
);


ALTER TABLE public.positions OWNER TO postgres;

--
-- TOC entry 200 (class 1259 OID 24603)
-- Name: user_flash; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.user_flash (
    user_name text,
    inv_name text,
    status text,
    date_flash date
);


ALTER TABLE public.user_flash OWNER TO postgres;

--
-- TOC entry 201 (class 1259 OID 24609)
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    login text,
    name text,
    pwd text
);


ALTER TABLE public.users OWNER TO postgres;

--
-- TOC entry 2922 (class 0 OID 24577)
-- Dependencies: 196
-- Data for Name: etat; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.etat VALUES (2032, 'PA_142', '30', 'no info', 'no info', 'http://invader.spotter.free.fr/images/PA_142-grosplan.png', 'None', 'None');



--
-- TOC entry 2923 (class 0 OID 24583)
-- Dependencies: 197
-- Data for Name: invit_users; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.invit_users VALUES ('test2', 'AAAAA', 'normal', 'en attente');


--
-- TOC entry 2924 (class 0 OID 24591)
-- Dependencies: 198
-- Data for Name: modif_state_user; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.modif_state_user VALUES ('test', 'PA_960', 'detruit', '2021-02-19');


--
-- TOC entry 2925 (class 0 OID 24597)
-- Dependencies: 199
-- Data for Name: positions; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.positions VALUES ('LDN_1', 51.5243797, -0.214141995, 30, 'http://invader.spotter.free.fr/images/LDN_001-grosplan.png|http://invader.spotter.free.fr/photos/LDN_001-mai2017.jpg');


--
-- TOC entry 2926 (class 0 OID 24603)
-- Dependencies: 200
-- Data for Name: user_flash; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.user_flash VALUES ('test', 'PA_1043', 'flash', '2021-02-19');


--
-- TOC entry 2927 (class 0 OID 24609)
-- Dependencies: 201
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.users VALUES ('test', 'test', 'a94a8fe5ccb19ba61c4c0873d391e987982fbbd3');


--
-- TOC entry 2934 (class 0 OID 0)
-- Dependencies: 196
-- Name: TABLE etat; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.etat TO invader;


--
-- TOC entry 2935 (class 0 OID 0)
-- Dependencies: 197
-- Name: TABLE invit_users; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.invit_users TO invader;


--
-- TOC entry 2936 (class 0 OID 0)
-- Dependencies: 198
-- Name: TABLE modif_state_user; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.modif_state_user TO invader;


--
-- TOC entry 2937 (class 0 OID 0)
-- Dependencies: 199
-- Name: TABLE positions; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.positions TO invader;


--
-- TOC entry 2938 (class 0 OID 0)
-- Dependencies: 200
-- Name: TABLE user_flash; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.user_flash TO invader;


--
-- TOC entry 2939 (class 0 OID 0)
-- Dependencies: 201
-- Name: TABLE users; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,UPDATE ON TABLE public.users TO invader;


-- Completed on 2021-02-19 18:49:31

--
-- PostgreSQL database dump complete
--

