CREATE TABLE IF NOT EXISTS public.sessions
(
    id character varying(255) COLLATE pg_catalog."default" NOT NULL,
    user_id bigint,
    ip_address character varying(45) COLLATE pg_catalog."default",
    user_agent text COLLATE pg_catalog."default",
    payload text COLLATE pg_catalog."default" NOT NULL,
    last_activity integer NOT NULL,
    CONSTRAINT sessions_pkey PRIMARY KEY (id)
)

TABLESPACE pg_default;

ALTER TABLE public.sessions
    OWNER to postgres;

-- Index: public.sessions_last_activity_index
CREATE INDEX IF NOT EXISTS sessions_last_activity_index
    ON public.sessions USING btree
    (last_activity ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.sessions_user_id_index
CREATE INDEX IF NOT EXISTS sessions_user_id_index
    ON public.sessions USING btree
    (user_id ASC NULLS LAST)
    TABLESPACE pg_default;