CREATE TABLE IF NOT EXISTS public.cache_locks
(
    key character varying(255) COLLATE pg_catalog."default" NOT NULL,
    owner character varying(255) COLLATE pg_catalog."default" NOT NULL,
    expiration integer NOT NULL,
    CONSTRAINT cache_locks_pkey PRIMARY KEY (key)
)

TABLESPACE pg_default;

ALTER TABLE public.cache_locks
    OWNER to postgres;
