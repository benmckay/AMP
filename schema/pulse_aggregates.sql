CREATE TABLE IF NOT EXISTS public.pulse_aggregates
(
    id bigint NOT NULL DEFAULT nextval('pulse_aggregates_id_seq'::regclass),
    bucket integer NOT NULL,
    period integer NOT NULL,
    type character varying(255) COLLATE pg_catalog."default" NOT NULL,
    key text COLLATE pg_catalog."default" NOT NULL,
    key_hash uuid NOT NULL,
    aggregate character varying(255) COLLATE pg_catalog."default" NOT NULL,
    value numeric(20,2) NOT NULL,
    count integer,
    CONSTRAINT pulse_aggregates_pkey PRIMARY KEY (id),
    CONSTRAINT pulse_aggregates_bucket_period_type_aggregate_key_hash_unique UNIQUE (bucket, period, type, aggregate, key_hash)
)

TABLESPACE pg_default;

ALTER TABLE public.pulse_aggregates
    OWNER to postgres;

-- Index: public.pulse_aggregates_period_bucket_index
CREATE INDEX IF NOT EXISTS pulse_aggregates_period_bucket_index
    ON public.pulse_aggregates USING btree
    (period ASC NULLS LAST, bucket ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.pulse_aggregates_period_type_aggregate_bucket_index
CREATE INDEX IF NOT EXISTS pulse_aggregates_period_type_aggregate_bucket_index
    ON public.pulse_aggregates USING btree
    (period ASC NULLS LAST, type COLLATE pg_catalog."default" ASC NULLS LAST, aggregate COLLATE pg_catalog."default" ASC NULLS LAST, bucket ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.pulse_aggregates_type_index
CREATE INDEX IF NOT EXISTS pulse_aggregates_type_index
    ON public.pulse_aggregates USING btree
    (type COLLATE pg_catalog."default" ASC NULLS LAST)
    TABLESPACE pg_default;