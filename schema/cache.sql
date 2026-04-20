CREATE TABLE IF NOT EXISTS public.cache
(
    key character varying(255) COLLATE pg_catalog."default" NOT NULL,
    value text COLLATE pg_catalog."default" NOT NULL,
    expiration integer NOT NULL,
    CONSTRAINT cache_pkey PRIMARY KEY (key)
)

TABLESPACE pg_default;

ALTER TABLE public.cache
    OWNER to postgres;
