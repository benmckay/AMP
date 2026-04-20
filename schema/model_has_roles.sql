CREATE TABLE IF NOT EXISTS public.model_has_roles
(
    role_id bigint NOT NULL,
    model_type character varying(255) COLLATE pg_catalog."default" NOT NULL,
    model_id bigint NOT NULL,
    CONSTRAINT model_has_roles_pkey PRIMARY KEY (role_id, model_id, model_type),
    CONSTRAINT model_has_roles_role_id_foreign FOREIGN KEY (role_id)
        REFERENCES public.roles (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE CASCADE
)

TABLESPACE pg_default;

ALTER TABLE public.model_has_roles
    OWNER to postgres;

-- Index: public.model_has_roles_model_id_model_type_index
CREATE INDEX IF NOT EXISTS model_has_roles_model_id_model_type_index
    ON public.model_has_roles USING btree
    (model_id ASC NULLS LAST, model_type COLLATE pg_catalog."default" ASC NULLS LAST)
    TABLESPACE pg_default;