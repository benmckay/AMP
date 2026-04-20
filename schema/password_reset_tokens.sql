CREATE TABLE IF NOT EXISTS public.password_reset_tokens
(
    email character varying(255) COLLATE pg_catalog."default" NOT NULL,
    token character varying(255) COLLATE pg_catalog."default" NOT NULL,
    created_at timestamp(0) without time zone,
    CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email)
)

TABLESPACE pg_default;

ALTER TABLE public.password_reset_tokens
    OWNER to postgres;
