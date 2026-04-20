CREATE TABLE IF NOT EXISTS public.model_has_permissions
(
    permission_id bigint NOT NULL,
    model_type character varying(255) COLLATE pg_catalog."default" NOT NULL,
    model_id bigint NOT NULL,
    CONSTRAINT model_has_permissions_pkey PRIMARY KEY (permission_id, model_id, model_type),
    CONSTRAINT model_has_permissions_permission_id_foreign FOREIGN KEY (permission_id)
        REFERENCES public.permissions (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE CASCADE
)

TABLESPACE pg_default;

ALTER TABLE public.model_has_permissions
    OWNER to postgres;

-- Index: public.model_has_permissions_model_id_model_type_index
CREATE INDEX IF NOT EXISTS model_has_permissions_model_id_model_type_index
    ON public.model_has_permissions USING btree
    (model_id ASC NULLS LAST, model_type COLLATE pg_catalog."default" ASC NULLS LAST)
    TABLESPACE pg_default;