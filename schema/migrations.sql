CREATE TABLE IF NOT EXISTS public.migrations
(
    id integer NOT NULL DEFAULT nextval('migrations_id_seq'::regclass),
    migration character varying(255) COLLATE pg_catalog."default" NOT NULL,
    batch integer NOT NULL,
    CONSTRAINT migrations_pkey PRIMARY KEY (id)
)

TABLESPACE pg_default;

ALTER TABLE public.migrations
    OWNER to postgres;
