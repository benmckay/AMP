CREATE TABLE IF NOT EXISTS public.templates
(
    id bigint NOT NULL DEFAULT nextval('templates_id_seq'::regclass),
    mnemonic character varying(50) COLLATE pg_catalog."default" NOT NULL,
    name character varying(255) COLLATE pg_catalog."default" NOT NULL,
    department_id bigint NOT NULL,
    category character varying(100) COLLATE pg_catalog."default",
    description text COLLATE pg_catalog."default",
    ehr_access_level character varying(50) COLLATE pg_catalog."default" NOT NULL DEFAULT 'standard'::character varying,
    ehr_module_access jsonb NOT NULL DEFAULT '{}'::jsonb,
    ehr_permissions jsonb NOT NULL DEFAULT '{}'::jsonb,
    system_access jsonb NOT NULL DEFAULT '[]'::jsonb,
    permissions jsonb NOT NULL DEFAULT '{}'::jsonb,
    is_active boolean NOT NULL DEFAULT true,
    requires_cos_approval boolean NOT NULL DEFAULT false,
    created_by bigint,
    version integer NOT NULL DEFAULT 1,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    CONSTRAINT templates_pkey PRIMARY KEY (id),
    CONSTRAINT templates_department_mnemonic_unique UNIQUE (department_id, mnemonic),
    CONSTRAINT templates_created_by_foreign FOREIGN KEY (created_by)
        REFERENCES public.users (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE SET NULL,
    CONSTRAINT templates_department_id_foreign FOREIGN KEY (department_id)
        REFERENCES public.departments (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE CASCADE
)

TABLESPACE pg_default;

ALTER TABLE public.templates
    OWNER to postgres;

-- Index: public.idx_templates_ehr_modules
CREATE INDEX IF NOT EXISTS idx_templates_ehr_modules
    ON public.templates USING gin
    (ehr_module_access)
    TABLESPACE pg_default;
-- Index: public.idx_templates_system_access
CREATE INDEX IF NOT EXISTS idx_templates_system_access
    ON public.templates USING gin
    (system_access)
    TABLESPACE pg_default;
-- Index: public.templates_category_index
CREATE INDEX IF NOT EXISTS templates_category_index
    ON public.templates USING btree
    (category COLLATE pg_catalog."default" ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.templates_department_id_index
CREATE INDEX IF NOT EXISTS templates_department_id_index
    ON public.templates USING btree
    (department_id ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.templates_ehr_access_level_index
CREATE INDEX IF NOT EXISTS templates_ehr_access_level_index
    ON public.templates USING btree
    (ehr_access_level COLLATE pg_catalog."default" ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.templates_is_active_index
CREATE INDEX IF NOT EXISTS templates_is_active_index
    ON public.templates USING btree
    (is_active ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.templates_mnemonic_index
CREATE INDEX IF NOT EXISTS templates_mnemonic_index
    ON public.templates USING btree
    (mnemonic COLLATE pg_catalog."default" ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.templates_name_index
CREATE INDEX IF NOT EXISTS templates_name_index
    ON public.templates USING btree
    (name COLLATE pg_catalog."default" ASC NULLS LAST)
    TABLESPACE pg_default;