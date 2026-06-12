--
-- PostgreSQL database dump
--

\restrict SYDBf2wTarJdPPOmcbrAaXLgkxrJL1UUgIfvtqqkpHiv7zY6nKKozdysRdsY4AP

-- Dumped from database version 16.14 (Ubuntu 16.14-0ubuntu0.24.04.1)
-- Dumped by pg_dump version 16.14 (Ubuntu 16.14-0ubuntu0.24.04.1)

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

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: account_categories; Type: TABLE; Schema: public; Owner: baseri
--

CREATE TABLE public.account_categories (
    id bigint NOT NULL,
    parent_id bigint,
    jenis character varying(255) NOT NULL,
    nama character varying(120) NOT NULL,
    kod character varying(30),
    berulang boolean DEFAULT false NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    keterangan text,
    susunan integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT account_categories_jenis_check CHECK (((jenis)::text = ANY ((ARRAY['pendapatan'::character varying, 'perbelanjaan'::character varying])::text[])))
);


ALTER TABLE public.account_categories OWNER TO baseri;

--
-- Name: account_categories_id_seq; Type: SEQUENCE; Schema: public; Owner: baseri
--

CREATE SEQUENCE public.account_categories_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.account_categories_id_seq OWNER TO baseri;

--
-- Name: account_categories_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: baseri
--

ALTER SEQUENCE public.account_categories_id_seq OWNED BY public.account_categories.id;


--
-- Name: account_entries; Type: TABLE; Schema: public; Owner: baseri
--

CREATE TABLE public.account_entries (
    id bigint NOT NULL,
    category_id bigint NOT NULL,
    jenis character varying(255) NOT NULL,
    member_id bigint,
    amaun numeric(14,2) NOT NULL,
    tarikh date NOT NULL,
    rujukan character varying(60),
    penerima_pembayar character varying(150),
    keterangan text,
    recorded_by bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT account_entries_jenis_check CHECK (((jenis)::text = ANY ((ARRAY['pendapatan'::character varying, 'perbelanjaan'::character varying])::text[])))
);


ALTER TABLE public.account_entries OWNER TO baseri;

--
-- Name: account_entries_id_seq; Type: SEQUENCE; Schema: public; Owner: baseri
--

CREATE SEQUENCE public.account_entries_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.account_entries_id_seq OWNER TO baseri;

--
-- Name: account_entries_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: baseri
--

ALTER SEQUENCE public.account_entries_id_seq OWNED BY public.account_entries.id;


--
-- Name: cache; Type: TABLE; Schema: public; Owner: baseri
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration bigint NOT NULL
);


ALTER TABLE public.cache OWNER TO baseri;

--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: baseri
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration bigint NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO baseri;

--
-- Name: dividend_allocations; Type: TABLE; Schema: public; Owner: baseri
--

CREATE TABLE public.dividend_allocations (
    id bigint NOT NULL,
    dividend_run_id bigint NOT NULL,
    nama_tabung character varying(120) NOT NULL,
    jenis_kira character varying(255) DEFAULT 'peratus'::character varying NOT NULL,
    nilai numeric(14,2) DEFAULT '0'::numeric NOT NULL,
    amaun numeric(14,2) DEFAULT '0'::numeric NOT NULL,
    susunan integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT dividend_allocations_jenis_kira_check CHECK (((jenis_kira)::text = ANY ((ARRAY['peratus'::character varying, 'amaun'::character varying])::text[])))
);


ALTER TABLE public.dividend_allocations OWNER TO baseri;

--
-- Name: dividend_allocations_id_seq; Type: SEQUENCE; Schema: public; Owner: baseri
--

CREATE SEQUENCE public.dividend_allocations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.dividend_allocations_id_seq OWNER TO baseri;

--
-- Name: dividend_allocations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: baseri
--

ALTER SEQUENCE public.dividend_allocations_id_seq OWNED BY public.dividend_allocations.id;


--
-- Name: dividend_runs; Type: TABLE; Schema: public; Owner: baseri
--

CREATE TABLE public.dividend_runs (
    id bigint NOT NULL,
    tahun smallint NOT NULL,
    tarikh_cutoff date NOT NULL,
    untung_bersih numeric(14,2) DEFAULT '0'::numeric NOT NULL,
    jumlah_peruntukan numeric(14,2) DEFAULT '0'::numeric NOT NULL,
    untung_boleh_agih numeric(14,2) DEFAULT '0'::numeric NOT NULL,
    peratus_dividen numeric(5,2) DEFAULT '0'::numeric NOT NULL,
    jumlah_dividen numeric(14,2) DEFAULT '0'::numeric NOT NULL,
    status character varying(255) DEFAULT 'draf'::character varying NOT NULL,
    tarikh_muktamad date,
    dikira_oleh bigint,
    catatan text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    tarikh_mula date,
    jumlah_saham_anggota numeric(16,2) DEFAULT '0'::numeric NOT NULL,
    peratus_auditor numeric(5,2) DEFAULT '0'::numeric NOT NULL,
    peratus_diluluskan numeric(5,2) DEFAULT '0'::numeric NOT NULL,
    baki_dibawa_hadapan numeric(16,2) DEFAULT '0'::numeric NOT NULL,
    CONSTRAINT dividend_runs_status_check CHECK (((status)::text = ANY ((ARRAY['draf'::character varying, 'dimuktamadkan'::character varying])::text[])))
);


ALTER TABLE public.dividend_runs OWNER TO baseri;

--
-- Name: dividend_runs_id_seq; Type: SEQUENCE; Schema: public; Owner: baseri
--

CREATE SEQUENCE public.dividend_runs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.dividend_runs_id_seq OWNER TO baseri;

--
-- Name: dividend_runs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: baseri
--

ALTER SEQUENCE public.dividend_runs_id_seq OWNED BY public.dividend_runs.id;


--
-- Name: dividend_shares; Type: TABLE; Schema: public; Owner: baseri
--

CREATE TABLE public.dividend_shares (
    id bigint NOT NULL,
    dividend_run_id bigint NOT NULL,
    member_id bigint NOT NULL,
    saham_layak numeric(14,2) DEFAULT '0'::numeric NOT NULL,
    saham_auto numeric(14,2) DEFAULT '0'::numeric NOT NULL,
    peratus numeric(8,4) DEFAULT '0'::numeric NOT NULL,
    amaun_dividen numeric(14,2) DEFAULT '0'::numeric NOT NULL,
    override boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.dividend_shares OWNER TO baseri;

--
-- Name: dividend_shares_id_seq; Type: SEQUENCE; Schema: public; Owner: baseri
--

CREATE SEQUENCE public.dividend_shares_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.dividend_shares_id_seq OWNER TO baseri;

--
-- Name: dividend_shares_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: baseri
--

ALTER SEQUENCE public.dividend_shares_id_seq OWNED BY public.dividend_shares.id;


--
-- Name: loans; Type: TABLE; Schema: public; Owner: baseri
--

CREATE TABLE public.loans (
    id bigint NOT NULL,
    member_id bigint NOT NULL,
    dimohon_oleh bigint,
    amount numeric(12,2) NOT NULL,
    tempoh smallint NOT NULL,
    tujuan text NOT NULL,
    status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    catatan text,
    reviewed_by bigint,
    reviewed_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    meeting_id bigint,
    pencadang_id bigint,
    penyokong_id bigint,
    CONSTRAINT loans_status_check CHECK (((status)::text = ANY ((ARRAY['pending'::character varying, 'approved'::character varying, 'rejected'::character varying])::text[])))
);


ALTER TABLE public.loans OWNER TO baseri;

--
-- Name: loans_id_seq; Type: SEQUENCE; Schema: public; Owner: baseri
--

CREATE SEQUENCE public.loans_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.loans_id_seq OWNER TO baseri;

--
-- Name: loans_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: baseri
--

ALTER SEQUENCE public.loans_id_seq OWNED BY public.loans.id;


--
-- Name: meetings; Type: TABLE; Schema: public; Owner: baseri
--

CREATE TABLE public.meetings (
    id bigint NOT NULL,
    tajuk character varying(255) NOT NULL,
    tarikh date NOT NULL,
    lokasi character varying(255),
    minit text,
    created_by bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.meetings OWNER TO baseri;

--
-- Name: meetings_id_seq; Type: SEQUENCE; Schema: public; Owner: baseri
--

CREATE SEQUENCE public.meetings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.meetings_id_seq OWNER TO baseri;

--
-- Name: meetings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: baseri
--

ALTER SEQUENCE public.meetings_id_seq OWNED BY public.meetings.id;


--
-- Name: members; Type: TABLE; Schema: public; Owner: baseri
--

CREATE TABLE public.members (
    id bigint NOT NULL,
    no_ahli character varying(10) NOT NULL,
    user_id bigint,
    nama character varying(255) NOT NULL,
    no_kp character varying(20),
    telefon character varying(20),
    alamat text,
    tarikh_sertai date,
    status character varying(255) DEFAULT 'aktif'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    foto_path character varying(255),
    CONSTRAINT members_status_check CHECK (((status)::text = ANY ((ARRAY['aktif'::character varying, 'tidak_aktif'::character varying, 'berhenti'::character varying])::text[])))
);


ALTER TABLE public.members OWNER TO baseri;

--
-- Name: members_id_seq; Type: SEQUENCE; Schema: public; Owner: baseri
--

CREATE SEQUENCE public.members_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.members_id_seq OWNER TO baseri;

--
-- Name: members_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: baseri
--

ALTER SEQUENCE public.members_id_seq OWNED BY public.members.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: baseri
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO baseri;

--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: baseri
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO baseri;

--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: baseri
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: module_role; Type: TABLE; Schema: public; Owner: baseri
--

CREATE TABLE public.module_role (
    module_key character varying(50) NOT NULL,
    role_id bigint NOT NULL
);


ALTER TABLE public.module_role OWNER TO baseri;

--
-- Name: next_of_kin; Type: TABLE; Schema: public; Owner: baseri
--

CREATE TABLE public.next_of_kin (
    id bigint NOT NULL,
    member_id bigint NOT NULL,
    nama character varying(255) NOT NULL,
    no_kp character varying(20),
    hubungan character varying(50) NOT NULL,
    telefon character varying(20),
    alamat text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.next_of_kin OWNER TO baseri;

--
-- Name: next_of_kin_id_seq; Type: SEQUENCE; Schema: public; Owner: baseri
--

CREATE SEQUENCE public.next_of_kin_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.next_of_kin_id_seq OWNER TO baseri;

--
-- Name: next_of_kin_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: baseri
--

ALTER SEQUENCE public.next_of_kin_id_seq OWNED BY public.next_of_kin.id;


--
-- Name: ownership_transfers; Type: TABLE; Schema: public; Owner: baseri
--

CREATE TABLE public.ownership_transfers (
    id bigint NOT NULL,
    member_id bigint NOT NULL,
    from_user_id bigint,
    from_nama character varying(255),
    to_user_id bigint,
    to_nama character varying(255) NOT NULL,
    to_no_kp character varying(20),
    sebab character varying(100),
    tarikh_pindah date NOT NULL,
    processed_by bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    meeting_id bigint,
    pencadang_id bigint,
    penyokong_id bigint,
    catatan_kelulusan text
);


ALTER TABLE public.ownership_transfers OWNER TO baseri;

--
-- Name: ownership_transfers_id_seq; Type: SEQUENCE; Schema: public; Owner: baseri
--

CREATE SEQUENCE public.ownership_transfers_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.ownership_transfers_id_seq OWNER TO baseri;

--
-- Name: ownership_transfers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: baseri
--

ALTER SEQUENCE public.ownership_transfers_id_seq OWNED BY public.ownership_transfers.id;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: baseri
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO baseri;

--
-- Name: permission_role; Type: TABLE; Schema: public; Owner: baseri
--

CREATE TABLE public.permission_role (
    permission_id bigint NOT NULL,
    role_id bigint NOT NULL
);


ALTER TABLE public.permission_role OWNER TO baseri;

--
-- Name: permissions; Type: TABLE; Schema: public; Owner: baseri
--

CREATE TABLE public.permissions (
    id bigint NOT NULL,
    name character varying(50) NOT NULL,
    slug character varying(50) NOT NULL,
    description text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.permissions OWNER TO baseri;

--
-- Name: permissions_id_seq; Type: SEQUENCE; Schema: public; Owner: baseri
--

CREATE SEQUENCE public.permissions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.permissions_id_seq OWNER TO baseri;

--
-- Name: permissions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: baseri
--

ALTER SEQUENCE public.permissions_id_seq OWNED BY public.permissions.id;


--
-- Name: role_user; Type: TABLE; Schema: public; Owner: baseri
--

CREATE TABLE public.role_user (
    user_id bigint NOT NULL,
    role_id bigint NOT NULL
);


ALTER TABLE public.role_user OWNER TO baseri;

--
-- Name: roles; Type: TABLE; Schema: public; Owner: baseri
--

CREATE TABLE public.roles (
    id bigint NOT NULL,
    name character varying(50) NOT NULL,
    slug character varying(50) NOT NULL,
    description text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.roles OWNER TO baseri;

--
-- Name: roles_id_seq; Type: SEQUENCE; Schema: public; Owner: baseri
--

CREATE SEQUENCE public.roles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.roles_id_seq OWNER TO baseri;

--
-- Name: roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: baseri
--

ALTER SEQUENCE public.roles_id_seq OWNED BY public.roles.id;


--
-- Name: savings; Type: TABLE; Schema: public; Owner: baseri
--

CREATE TABLE public.savings (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    jenis character varying(255) NOT NULL,
    amaun numeric(12,2) NOT NULL,
    recorded_by bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT savings_jenis_check CHECK (((jenis)::text = ANY ((ARRAY['simpanan'::character varying, 'saham'::character varying])::text[])))
);


ALTER TABLE public.savings OWNER TO baseri;

--
-- Name: savings_id_seq; Type: SEQUENCE; Schema: public; Owner: baseri
--

CREATE SEQUENCE public.savings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.savings_id_seq OWNER TO baseri;

--
-- Name: savings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: baseri
--

ALTER SEQUENCE public.savings_id_seq OWNED BY public.savings.id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: baseri
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO baseri;

--
-- Name: settings; Type: TABLE; Schema: public; Owner: baseri
--

CREATE TABLE public.settings (
    id bigint NOT NULL,
    key character varying(80) NOT NULL,
    value text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.settings OWNER TO baseri;

--
-- Name: settings_id_seq; Type: SEQUENCE; Schema: public; Owner: baseri
--

CREATE SEQUENCE public.settings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.settings_id_seq OWNER TO baseri;

--
-- Name: settings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: baseri
--

ALTER SEQUENCE public.settings_id_seq OWNED BY public.settings.id;


--
-- Name: share_transfers; Type: TABLE; Schema: public; Owner: baseri
--

CREATE TABLE public.share_transfers (
    id bigint NOT NULL,
    from_member_id bigint NOT NULL,
    to_member_id bigint NOT NULL,
    amaun numeric(12,2) NOT NULL,
    sebab character varying(100),
    tarikh_pindah date NOT NULL,
    processed_by bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    meeting_id bigint,
    pencadang_id bigint,
    penyokong_id bigint,
    catatan_kelulusan text
);


ALTER TABLE public.share_transfers OWNER TO baseri;

--
-- Name: share_transfers_id_seq; Type: SEQUENCE; Schema: public; Owner: baseri
--

CREATE SEQUENCE public.share_transfers_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.share_transfers_id_seq OWNER TO baseri;

--
-- Name: share_transfers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: baseri
--

ALTER SEQUENCE public.share_transfers_id_seq OWNED BY public.share_transfers.id;


--
-- Name: transactions; Type: TABLE; Schema: public; Owner: baseri
--

CREATE TABLE public.transactions (
    id bigint NOT NULL,
    member_id bigint NOT NULL,
    jenis character varying(255) NOT NULL,
    arah character varying(255) NOT NULL,
    amaun numeric(12,2) NOT NULL,
    baki numeric(12,2) NOT NULL,
    sumber character varying(30) DEFAULT 'manual'::character varying NOT NULL,
    rujukan character varying(50),
    keterangan text,
    recorded_by bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT transactions_arah_check CHECK (((arah)::text = ANY ((ARRAY['masuk'::character varying, 'keluar'::character varying])::text[]))),
    CONSTRAINT transactions_jenis_check CHECK (((jenis)::text = ANY ((ARRAY['saham'::character varying, 'simpanan'::character varying])::text[])))
);


ALTER TABLE public.transactions OWNER TO baseri;

--
-- Name: transactions_id_seq; Type: SEQUENCE; Schema: public; Owner: baseri
--

CREATE SEQUENCE public.transactions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.transactions_id_seq OWNER TO baseri;

--
-- Name: transactions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: baseri
--

ALTER SEQUENCE public.transactions_id_seq OWNED BY public.transactions.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: baseri
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    phone character varying(20),
    password character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    remember_token character varying(100),
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    avatar_path character varying(255)
);


ALTER TABLE public.users OWNER TO baseri;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: baseri
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO baseri;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: baseri
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: account_categories id; Type: DEFAULT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.account_categories ALTER COLUMN id SET DEFAULT nextval('public.account_categories_id_seq'::regclass);


--
-- Name: account_entries id; Type: DEFAULT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.account_entries ALTER COLUMN id SET DEFAULT nextval('public.account_entries_id_seq'::regclass);


--
-- Name: dividend_allocations id; Type: DEFAULT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.dividend_allocations ALTER COLUMN id SET DEFAULT nextval('public.dividend_allocations_id_seq'::regclass);


--
-- Name: dividend_runs id; Type: DEFAULT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.dividend_runs ALTER COLUMN id SET DEFAULT nextval('public.dividend_runs_id_seq'::regclass);


--
-- Name: dividend_shares id; Type: DEFAULT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.dividend_shares ALTER COLUMN id SET DEFAULT nextval('public.dividend_shares_id_seq'::regclass);


--
-- Name: loans id; Type: DEFAULT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.loans ALTER COLUMN id SET DEFAULT nextval('public.loans_id_seq'::regclass);


--
-- Name: meetings id; Type: DEFAULT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.meetings ALTER COLUMN id SET DEFAULT nextval('public.meetings_id_seq'::regclass);


--
-- Name: members id; Type: DEFAULT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.members ALTER COLUMN id SET DEFAULT nextval('public.members_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: next_of_kin id; Type: DEFAULT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.next_of_kin ALTER COLUMN id SET DEFAULT nextval('public.next_of_kin_id_seq'::regclass);


--
-- Name: ownership_transfers id; Type: DEFAULT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.ownership_transfers ALTER COLUMN id SET DEFAULT nextval('public.ownership_transfers_id_seq'::regclass);


--
-- Name: permissions id; Type: DEFAULT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.permissions ALTER COLUMN id SET DEFAULT nextval('public.permissions_id_seq'::regclass);


--
-- Name: roles id; Type: DEFAULT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.roles ALTER COLUMN id SET DEFAULT nextval('public.roles_id_seq'::regclass);


--
-- Name: savings id; Type: DEFAULT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.savings ALTER COLUMN id SET DEFAULT nextval('public.savings_id_seq'::regclass);


--
-- Name: settings id; Type: DEFAULT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.settings ALTER COLUMN id SET DEFAULT nextval('public.settings_id_seq'::regclass);


--
-- Name: share_transfers id; Type: DEFAULT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.share_transfers ALTER COLUMN id SET DEFAULT nextval('public.share_transfers_id_seq'::regclass);


--
-- Name: transactions id; Type: DEFAULT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.transactions ALTER COLUMN id SET DEFAULT nextval('public.transactions_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: account_categories account_categories_pkey; Type: CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.account_categories
    ADD CONSTRAINT account_categories_pkey PRIMARY KEY (id);


--
-- Name: account_entries account_entries_pkey; Type: CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.account_entries
    ADD CONSTRAINT account_entries_pkey PRIMARY KEY (id);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: dividend_allocations dividend_allocations_pkey; Type: CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.dividend_allocations
    ADD CONSTRAINT dividend_allocations_pkey PRIMARY KEY (id);


--
-- Name: dividend_runs dividend_runs_pkey; Type: CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.dividend_runs
    ADD CONSTRAINT dividend_runs_pkey PRIMARY KEY (id);


--
-- Name: dividend_runs dividend_runs_tahun_unique; Type: CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.dividend_runs
    ADD CONSTRAINT dividend_runs_tahun_unique UNIQUE (tahun);


--
-- Name: dividend_shares dividend_shares_dividend_run_id_member_id_unique; Type: CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.dividend_shares
    ADD CONSTRAINT dividend_shares_dividend_run_id_member_id_unique UNIQUE (dividend_run_id, member_id);


--
-- Name: dividend_shares dividend_shares_pkey; Type: CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.dividend_shares
    ADD CONSTRAINT dividend_shares_pkey PRIMARY KEY (id);


--
-- Name: loans loans_pkey; Type: CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.loans
    ADD CONSTRAINT loans_pkey PRIMARY KEY (id);


--
-- Name: meetings meetings_pkey; Type: CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.meetings
    ADD CONSTRAINT meetings_pkey PRIMARY KEY (id);


--
-- Name: members members_no_ahli_unique; Type: CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.members
    ADD CONSTRAINT members_no_ahli_unique UNIQUE (no_ahli);


--
-- Name: members members_pkey; Type: CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.members
    ADD CONSTRAINT members_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: module_role module_role_pkey; Type: CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.module_role
    ADD CONSTRAINT module_role_pkey PRIMARY KEY (module_key, role_id);


--
-- Name: next_of_kin next_of_kin_member_id_unique; Type: CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.next_of_kin
    ADD CONSTRAINT next_of_kin_member_id_unique UNIQUE (member_id);


--
-- Name: next_of_kin next_of_kin_pkey; Type: CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.next_of_kin
    ADD CONSTRAINT next_of_kin_pkey PRIMARY KEY (id);


--
-- Name: ownership_transfers ownership_transfers_pkey; Type: CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.ownership_transfers
    ADD CONSTRAINT ownership_transfers_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: permission_role permission_role_pkey; Type: CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.permission_role
    ADD CONSTRAINT permission_role_pkey PRIMARY KEY (permission_id, role_id);


--
-- Name: permissions permissions_name_unique; Type: CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_name_unique UNIQUE (name);


--
-- Name: permissions permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_pkey PRIMARY KEY (id);


--
-- Name: permissions permissions_slug_unique; Type: CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_slug_unique UNIQUE (slug);


--
-- Name: role_user role_user_pkey; Type: CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.role_user
    ADD CONSTRAINT role_user_pkey PRIMARY KEY (user_id, role_id);


--
-- Name: roles roles_name_unique; Type: CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_name_unique UNIQUE (name);


--
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- Name: roles roles_slug_unique; Type: CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_slug_unique UNIQUE (slug);


--
-- Name: savings savings_pkey; Type: CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.savings
    ADD CONSTRAINT savings_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: settings settings_key_unique; Type: CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.settings
    ADD CONSTRAINT settings_key_unique UNIQUE (key);


--
-- Name: settings settings_pkey; Type: CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.settings
    ADD CONSTRAINT settings_pkey PRIMARY KEY (id);


--
-- Name: share_transfers share_transfers_pkey; Type: CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.share_transfers
    ADD CONSTRAINT share_transfers_pkey PRIMARY KEY (id);


--
-- Name: transactions transactions_pkey; Type: CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.transactions
    ADD CONSTRAINT transactions_pkey PRIMARY KEY (id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: account_categories_jenis_is_active_index; Type: INDEX; Schema: public; Owner: baseri
--

CREATE INDEX account_categories_jenis_is_active_index ON public.account_categories USING btree (jenis, is_active);


--
-- Name: account_categories_parent_id_index; Type: INDEX; Schema: public; Owner: baseri
--

CREATE INDEX account_categories_parent_id_index ON public.account_categories USING btree (parent_id);


--
-- Name: account_entries_category_id_index; Type: INDEX; Schema: public; Owner: baseri
--

CREATE INDEX account_entries_category_id_index ON public.account_entries USING btree (category_id);


--
-- Name: account_entries_jenis_tarikh_index; Type: INDEX; Schema: public; Owner: baseri
--

CREATE INDEX account_entries_jenis_tarikh_index ON public.account_entries USING btree (jenis, tarikh);


--
-- Name: account_entries_member_id_index; Type: INDEX; Schema: public; Owner: baseri
--

CREATE INDEX account_entries_member_id_index ON public.account_entries USING btree (member_id);


--
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: baseri
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: baseri
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- Name: dividend_shares_member_id_index; Type: INDEX; Schema: public; Owner: baseri
--

CREATE INDEX dividend_shares_member_id_index ON public.dividend_shares USING btree (member_id);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: baseri
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: baseri
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: transactions_member_id_jenis_index; Type: INDEX; Schema: public; Owner: baseri
--

CREATE INDEX transactions_member_id_jenis_index ON public.transactions USING btree (member_id, jenis);


--
-- Name: account_categories account_categories_parent_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.account_categories
    ADD CONSTRAINT account_categories_parent_id_foreign FOREIGN KEY (parent_id) REFERENCES public.account_categories(id) ON DELETE CASCADE;


--
-- Name: account_entries account_entries_category_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.account_entries
    ADD CONSTRAINT account_entries_category_id_foreign FOREIGN KEY (category_id) REFERENCES public.account_categories(id) ON DELETE RESTRICT;


--
-- Name: account_entries account_entries_member_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.account_entries
    ADD CONSTRAINT account_entries_member_id_foreign FOREIGN KEY (member_id) REFERENCES public.members(id) ON DELETE SET NULL;


--
-- Name: account_entries account_entries_recorded_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.account_entries
    ADD CONSTRAINT account_entries_recorded_by_foreign FOREIGN KEY (recorded_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: dividend_allocations dividend_allocations_dividend_run_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.dividend_allocations
    ADD CONSTRAINT dividend_allocations_dividend_run_id_foreign FOREIGN KEY (dividend_run_id) REFERENCES public.dividend_runs(id) ON DELETE CASCADE;


--
-- Name: dividend_runs dividend_runs_dikira_oleh_foreign; Type: FK CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.dividend_runs
    ADD CONSTRAINT dividend_runs_dikira_oleh_foreign FOREIGN KEY (dikira_oleh) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: dividend_shares dividend_shares_dividend_run_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.dividend_shares
    ADD CONSTRAINT dividend_shares_dividend_run_id_foreign FOREIGN KEY (dividend_run_id) REFERENCES public.dividend_runs(id) ON DELETE CASCADE;


--
-- Name: dividend_shares dividend_shares_member_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.dividend_shares
    ADD CONSTRAINT dividend_shares_member_id_foreign FOREIGN KEY (member_id) REFERENCES public.members(id) ON DELETE CASCADE;


--
-- Name: loans loans_dimohon_oleh_foreign; Type: FK CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.loans
    ADD CONSTRAINT loans_dimohon_oleh_foreign FOREIGN KEY (dimohon_oleh) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: loans loans_meeting_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.loans
    ADD CONSTRAINT loans_meeting_id_foreign FOREIGN KEY (meeting_id) REFERENCES public.meetings(id) ON DELETE SET NULL;


--
-- Name: loans loans_member_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.loans
    ADD CONSTRAINT loans_member_id_foreign FOREIGN KEY (member_id) REFERENCES public.members(id) ON DELETE CASCADE;


--
-- Name: loans loans_pencadang_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.loans
    ADD CONSTRAINT loans_pencadang_id_foreign FOREIGN KEY (pencadang_id) REFERENCES public.members(id) ON DELETE SET NULL;


--
-- Name: loans loans_penyokong_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.loans
    ADD CONSTRAINT loans_penyokong_id_foreign FOREIGN KEY (penyokong_id) REFERENCES public.members(id) ON DELETE SET NULL;


--
-- Name: loans loans_reviewed_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.loans
    ADD CONSTRAINT loans_reviewed_by_foreign FOREIGN KEY (reviewed_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: meetings meetings_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.meetings
    ADD CONSTRAINT meetings_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: members members_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.members
    ADD CONSTRAINT members_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: module_role module_role_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.module_role
    ADD CONSTRAINT module_role_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- Name: next_of_kin next_of_kin_member_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.next_of_kin
    ADD CONSTRAINT next_of_kin_member_id_foreign FOREIGN KEY (member_id) REFERENCES public.members(id) ON DELETE CASCADE;


--
-- Name: ownership_transfers ownership_transfers_from_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.ownership_transfers
    ADD CONSTRAINT ownership_transfers_from_user_id_foreign FOREIGN KEY (from_user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: ownership_transfers ownership_transfers_meeting_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.ownership_transfers
    ADD CONSTRAINT ownership_transfers_meeting_id_foreign FOREIGN KEY (meeting_id) REFERENCES public.meetings(id) ON DELETE SET NULL;


--
-- Name: ownership_transfers ownership_transfers_member_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.ownership_transfers
    ADD CONSTRAINT ownership_transfers_member_id_foreign FOREIGN KEY (member_id) REFERENCES public.members(id) ON DELETE CASCADE;


--
-- Name: ownership_transfers ownership_transfers_pencadang_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.ownership_transfers
    ADD CONSTRAINT ownership_transfers_pencadang_id_foreign FOREIGN KEY (pencadang_id) REFERENCES public.members(id) ON DELETE SET NULL;


--
-- Name: ownership_transfers ownership_transfers_penyokong_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.ownership_transfers
    ADD CONSTRAINT ownership_transfers_penyokong_id_foreign FOREIGN KEY (penyokong_id) REFERENCES public.members(id) ON DELETE SET NULL;


--
-- Name: ownership_transfers ownership_transfers_processed_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.ownership_transfers
    ADD CONSTRAINT ownership_transfers_processed_by_foreign FOREIGN KEY (processed_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: ownership_transfers ownership_transfers_to_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.ownership_transfers
    ADD CONSTRAINT ownership_transfers_to_user_id_foreign FOREIGN KEY (to_user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: permission_role permission_role_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.permission_role
    ADD CONSTRAINT permission_role_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- Name: permission_role permission_role_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.permission_role
    ADD CONSTRAINT permission_role_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- Name: role_user role_user_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.role_user
    ADD CONSTRAINT role_user_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- Name: role_user role_user_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.role_user
    ADD CONSTRAINT role_user_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: savings savings_recorded_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.savings
    ADD CONSTRAINT savings_recorded_by_foreign FOREIGN KEY (recorded_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: savings savings_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.savings
    ADD CONSTRAINT savings_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: share_transfers share_transfers_from_member_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.share_transfers
    ADD CONSTRAINT share_transfers_from_member_id_foreign FOREIGN KEY (from_member_id) REFERENCES public.members(id) ON DELETE CASCADE;


--
-- Name: share_transfers share_transfers_meeting_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.share_transfers
    ADD CONSTRAINT share_transfers_meeting_id_foreign FOREIGN KEY (meeting_id) REFERENCES public.meetings(id) ON DELETE SET NULL;


--
-- Name: share_transfers share_transfers_pencadang_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.share_transfers
    ADD CONSTRAINT share_transfers_pencadang_id_foreign FOREIGN KEY (pencadang_id) REFERENCES public.members(id) ON DELETE SET NULL;


--
-- Name: share_transfers share_transfers_penyokong_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.share_transfers
    ADD CONSTRAINT share_transfers_penyokong_id_foreign FOREIGN KEY (penyokong_id) REFERENCES public.members(id) ON DELETE SET NULL;


--
-- Name: share_transfers share_transfers_processed_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.share_transfers
    ADD CONSTRAINT share_transfers_processed_by_foreign FOREIGN KEY (processed_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: share_transfers share_transfers_to_member_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.share_transfers
    ADD CONSTRAINT share_transfers_to_member_id_foreign FOREIGN KEY (to_member_id) REFERENCES public.members(id) ON DELETE CASCADE;


--
-- Name: transactions transactions_member_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.transactions
    ADD CONSTRAINT transactions_member_id_foreign FOREIGN KEY (member_id) REFERENCES public.members(id) ON DELETE CASCADE;


--
-- Name: transactions transactions_recorded_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: baseri
--

ALTER TABLE ONLY public.transactions
    ADD CONSTRAINT transactions_recorded_by_foreign FOREIGN KEY (recorded_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: SCHEMA public; Type: ACL; Schema: -; Owner: pg_database_owner
--

GRANT ALL ON SCHEMA public TO baseri;


--
-- PostgreSQL database dump complete
--

\unrestrict SYDBf2wTarJdPPOmcbrAaXLgkxrJL1UUgIfvtqqkpHiv7zY6nKKozdysRdsY4AP

