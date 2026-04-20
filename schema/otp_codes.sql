CREATE TABLE IF NOT EXISTS public.otp_codes
(
    id bigint NOT NULL DEFAULT nextval('otp_codes_id_seq'::regclass),
    user_id bigint NOT NULL,
    phone_number character varying(30) COLLATE pg_catalog."default" NOT NULL,
    purpose character varying(50) COLLATE pg_catalog."default" NOT NULL DEFAULT 'login'::character varying,
    otp_hash character varying(255) COLLATE pg_catalog."default" NOT NULL,
    expires_at timestamp(0) without time zone NOT NULL,
    attempts smallint NOT NULL DEFAULT '0'::smallint,
    used_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT otp_codes_pkey PRIMARY KEY (id),
    CONSTRAINT otp_codes_user_id_foreign FOREIGN KEY (user_id)
        REFERENCES public.users (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE CASCADE
)

TABLESPACE pg_default;

ALTER TABLE public.otp_codes
    OWNER to postgres;

-- Index: public.otp_codes_expires_at_index
CREATE INDEX IF NOT EXISTS otp_codes_expires_at_index
    ON public.otp_codes USING btree
    (expires_at ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.otp_codes_phone_number_purpose_created_at_index
CREATE INDEX IF NOT EXISTS otp_codes_phone_number_purpose_created_at_index
    ON public.otp_codes USING btree
    (phone_number COLLATE pg_catalog."default" ASC NULLS LAST, purpose COLLATE pg_catalog."default" ASC NULLS LAST, created_at ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.otp_codes_user_id_purpose_created_at_index
CREATE INDEX IF NOT EXISTS otp_codes_user_id_purpose_created_at_index
    ON public.otp_codes USING btree
    (user_id ASC NULLS LAST, purpose COLLATE pg_catalog."default" ASC NULLS LAST, created_at ASC NULLS LAST)
    TABLESPACE pg_default;