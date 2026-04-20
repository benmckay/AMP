CREATE TABLE IF NOT EXISTS public.pulse_values
(
    id bigint NOT NULL DEFAULT nextval('pulse_values_id_seq'::regclass),
    "timestamp" integer NOT NULL,
    type character varying(255) COLLATE pg_catalog."default" NOT NULL,
    key text COLLATE pg_catalog."default" NOT NULL,
    key_hash uuid NOT NULL,
    value text COLLATE pg_catalog."default" NOT NULL,
    CONSTRAINT pulse_values_pkey PRIMARY KEY (id),
    CONSTRAINT pulse_values_type_key_hash_unique UNIQUE (type, key_hash)
)

TABLESPACE pg_default;

ALTER TABLE public.pulse_values
    OWNER to postgres;

-- Index: public.pulse_values_timestamp_index
CREATE INDEX IF NOT EXISTS pulse_values_timestamp_index
    ON public.pulse_values USING btree
    ("""timestamp""" ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.pulse_values_type_index
CREATE INDEX IF NOT EXISTS pulse_values_type_index
    ON public.pulse_values USING btree
    (type COLLATE pg_catalog."default" ASC NULLS LAST)
    TABLESPACE pg_default;