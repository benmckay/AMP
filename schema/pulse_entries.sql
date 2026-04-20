CREATE TABLE IF NOT EXISTS public.pulse_entries
(
    id bigint NOT NULL DEFAULT nextval('pulse_entries_id_seq'::regclass),
    "timestamp" integer NOT NULL,
    type character varying(255) COLLATE pg_catalog."default" NOT NULL,
    key text COLLATE pg_catalog."default" NOT NULL,
    key_hash uuid NOT NULL,
    value bigint,
    CONSTRAINT pulse_entries_pkey PRIMARY KEY (id)
)

TABLESPACE pg_default;

ALTER TABLE public.pulse_entries
    OWNER to postgres;

-- Index: public.pulse_entries_key_hash_index
CREATE INDEX IF NOT EXISTS pulse_entries_key_hash_index
    ON public.pulse_entries USING btree
    (key_hash ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.pulse_entries_timestamp_index
CREATE INDEX IF NOT EXISTS pulse_entries_timestamp_index
    ON public.pulse_entries USING btree
    ("""timestamp""" ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.pulse_entries_timestamp_type_key_hash_value_index
CREATE INDEX IF NOT EXISTS pulse_entries_timestamp_type_key_hash_value_index
    ON public.pulse_entries USING btree
    ("""timestamp""" ASC NULLS LAST, type COLLATE pg_catalog."default" ASC NULLS LAST, key_hash ASC NULLS LAST, value ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.pulse_entries_type_index
CREATE INDEX IF NOT EXISTS pulse_entries_type_index
    ON public.pulse_entries USING btree
    (type COLLATE pg_catalog."default" ASC NULLS LAST)
    TABLESPACE pg_default;