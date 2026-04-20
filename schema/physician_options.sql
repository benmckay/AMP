CREATE TABLE IF NOT EXISTS public.physician_options
(
    id bigint NOT NULL DEFAULT nextval('physician_options_id_seq'::regclass),
    type character varying(50) COLLATE pg_catalog."default" NOT NULL,
    value character varying(100) COLLATE pg_catalog."default" NOT NULL,
    is_active boolean NOT NULL DEFAULT true,
    sort_order smallint NOT NULL DEFAULT '0'::smallint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT physician_options_pkey PRIMARY KEY (id),
    CONSTRAINT physician_options_type_value_unique UNIQUE (type, value)
)

TABLESPACE pg_default;

ALTER TABLE public.physician_options
    OWNER to postgres;

-- Index: public.physician_options_type_is_active_index
CREATE INDEX IF NOT EXISTS physician_options_type_is_active_index
    ON public.physician_options USING btree
    (type COLLATE pg_catalog."default" ASC NULLS LAST, is_active ASC NULLS LAST)
    TABLESPACE pg_default;