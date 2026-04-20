CREATE TABLE IF NOT EXISTS public.audit_logs
(
    id bigint NOT NULL DEFAULT nextval('audit_logs_id_seq'::regclass),
    user_id bigint,
    action character varying(100) COLLATE pg_catalog."default" NOT NULL,
    model_type character varying(100) COLLATE pg_catalog."default",
    model_id bigint,
    changes jsonb,
    ip_address character varying(45) COLLATE pg_catalog."default",
    user_agent text COLLATE pg_catalog."default",
    created_at timestamp(0) without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT audit_logs_pkey PRIMARY KEY (id),
    CONSTRAINT audit_logs_user_id_foreign FOREIGN KEY (user_id)
        REFERENCES public.users (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE SET NULL
)

TABLESPACE pg_default;

ALTER TABLE public.audit_logs
    OWNER to postgres;

-- Index: public.audit_logs_action_index
CREATE INDEX IF NOT EXISTS audit_logs_action_index
    ON public.audit_logs USING btree
    (action COLLATE pg_catalog."default" ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.audit_logs_created_at_index
CREATE INDEX IF NOT EXISTS audit_logs_created_at_index
    ON public.audit_logs USING btree
    (created_at ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.audit_logs_model_type_model_id_index
CREATE INDEX IF NOT EXISTS audit_logs_model_type_model_id_index
    ON public.audit_logs USING btree
    (model_type COLLATE pg_catalog."default" ASC NULLS LAST, model_id ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.audit_logs_user_id_index
CREATE INDEX IF NOT EXISTS audit_logs_user_id_index
    ON public.audit_logs USING btree
    (user_id ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.idx_audit_logs_changes
CREATE INDEX IF NOT EXISTS idx_audit_logs_changes
    ON public.audit_logs USING gin
    (changes)
    TABLESPACE pg_default;