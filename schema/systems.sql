CREATE TABLE IF NOT EXISTS public.systems
(
    id bigint NOT NULL DEFAULT nextval('systems_id_seq'::regclass),
    code character varying(50) COLLATE pg_catalog."default" NOT NULL,
    name character varying(255) COLLATE pg_catalog."default" NOT NULL,
    description text COLLATE pg_catalog."default",
    is_active boolean NOT NULL DEFAULT true,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT systems_pkey PRIMARY KEY (id),
    CONSTRAINT systems_code_unique UNIQUE (code)
)

TABLESPACE pg_default;

ALTER TABLE public.systems
    OWNER to postgres;

-- Index: public.systems_code_index
CREATE INDEX IF NOT EXISTS systems_code_index
    ON public.systems USING btree
    (code COLLATE pg_catalog."default" ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.systems_is_active_index
CREATE INDEX IF NOT EXISTS systems_is_active_index
    ON public.systems USING btree
    (is_active ASC NULLS LAST)
    TABLESPACE pg_default;