CREATE TABLE IF NOT EXISTS public.departments
(
    id bigint NOT NULL DEFAULT nextval('departments_id_seq'::regclass),
    code character varying(50) COLLATE pg_catalog."default" NOT NULL,
    name character varying(255) COLLATE pg_catalog."default" NOT NULL,
    description text COLLATE pg_catalog."default",
    is_active boolean NOT NULL DEFAULT true,
    head_user_id bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    CONSTRAINT departments_pkey PRIMARY KEY (id),
    CONSTRAINT departments_code_unique UNIQUE (code),
    CONSTRAINT departments_head_user_id_foreign FOREIGN KEY (head_user_id)
        REFERENCES public.users (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE SET NULL
)

TABLESPACE pg_default;

ALTER TABLE public.departments
    OWNER to postgres;

-- Index: public.departments_code_index
CREATE INDEX IF NOT EXISTS departments_code_index
    ON public.departments USING btree
    (code COLLATE pg_catalog."default" ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.departments_is_active_index
CREATE INDEX IF NOT EXISTS departments_is_active_index
    ON public.departments USING btree
    (is_active ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.departments_name_index
CREATE INDEX IF NOT EXISTS departments_name_index
    ON public.departments USING btree
    (name COLLATE pg_catalog."default" ASC NULLS LAST)
    TABLESPACE pg_default;