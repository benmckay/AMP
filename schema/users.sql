CREATE TABLE IF NOT EXISTS public.users
(
    id bigint NOT NULL DEFAULT nextval('users_id_seq'::regclass),
    name character varying(255) COLLATE pg_catalog."default" NOT NULL,
    email character varying(255) COLLATE pg_catalog."default" NOT NULL,
    phone character varying(255) COLLATE pg_catalog."default",
    payroll_number character varying(255) COLLATE pg_catalog."default",
    department character varying(255) COLLATE pg_catalog."default",
    email_verified_at timestamp(0) without time zone,
    password character varying(255) COLLATE pg_catalog."default" NOT NULL,
    remember_token character varying(100) COLLATE pg_catalog."default",
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    two_factor_secret text COLLATE pg_catalog."default",
    two_factor_confirmed_at timestamp(0) without time zone,
    settings json,
    is_active boolean NOT NULL DEFAULT true,
    preferred_timezone character varying(64) COLLATE pg_catalog."default",
    preferred_language character varying(10) COLLATE pg_catalog."default" NOT NULL DEFAULT 'en'::character varying,
    theme character varying(20) COLLATE pg_catalog."default" NOT NULL DEFAULT 'system'::character varying,
    notify_security_alerts boolean NOT NULL DEFAULT true,
    notify_request_updates boolean NOT NULL DEFAULT true,
    notify_weekly_summary boolean NOT NULL DEFAULT false,
    CONSTRAINT users_pkey PRIMARY KEY (id),
    CONSTRAINT users_email_unique UNIQUE (email),
    CONSTRAINT users_payroll_number_unique UNIQUE (payroll_number)
)

TABLESPACE pg_default;

ALTER TABLE public.users
    OWNER to postgres;

-- Index: public.users_is_active_index
CREATE INDEX IF NOT EXISTS users_is_active_index
    ON public.users USING btree
    (is_active ASC NULLS LAST)
    TABLESPACE pg_default;