CREATE TABLE IF NOT EXISTS public.personal_access_tokens
(
    id bigint NOT NULL DEFAULT nextval('personal_access_tokens_id_seq'::regclass),
    tokenable_type character varying(255) COLLATE pg_catalog."default" NOT NULL,
    tokenable_id bigint NOT NULL,
    name text COLLATE pg_catalog."default" NOT NULL,
    token character varying(64) COLLATE pg_catalog."default" NOT NULL,
    abilities text COLLATE pg_catalog."default",
    last_used_at timestamp(0) without time zone,
    expires_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT personal_access_tokens_pkey PRIMARY KEY (id),
    CONSTRAINT personal_access_tokens_token_unique UNIQUE (token)
)

TABLESPACE pg_default;

ALTER TABLE public.personal_access_tokens
    OWNER to postgres;

-- Index: public.personal_access_tokens_expires_at_index
CREATE INDEX IF NOT EXISTS personal_access_tokens_expires_at_index
    ON public.personal_access_tokens USING btree
    (expires_at ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.personal_access_tokens_tokenable_type_tokenable_id_index
CREATE INDEX IF NOT EXISTS personal_access_tokens_tokenable_type_tokenable_id_index
    ON public.personal_access_tokens USING btree
    (tokenable_type COLLATE pg_catalog."default" ASC NULLS LAST, tokenable_id ASC NULLS LAST)
    TABLESPACE pg_default;