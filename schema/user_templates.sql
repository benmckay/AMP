CREATE TABLE IF NOT EXISTS public.user_templates
(
    id bigint NOT NULL DEFAULT nextval('user_templates_id_seq'::regclass),
    user_id bigint NOT NULL,
    template_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT user_templates_pkey PRIMARY KEY (id),
    CONSTRAINT user_templates_template_id_foreign FOREIGN KEY (template_id)
        REFERENCES public.templates (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION,
    CONSTRAINT user_templates_user_id_foreign FOREIGN KEY (user_id)
        REFERENCES public.users (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION
)

TABLESPACE pg_default;

ALTER TABLE public.user_templates
    OWNER to postgres;
