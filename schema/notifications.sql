CREATE TABLE IF NOT EXISTS public.notifications
(
    id uuid NOT NULL,
    type character varying(255) COLLATE pg_catalog."default" NOT NULL,
    notifiable_type character varying(255) COLLATE pg_catalog."default" NOT NULL,
    notifiable_id bigint NOT NULL,
    data text COLLATE pg_catalog."default" NOT NULL,
    read_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT notifications_pkey PRIMARY KEY (id)
)

TABLESPACE pg_default;

ALTER TABLE public.notifications
    OWNER to postgres;

-- Index: public.notifications_notifiable_type_notifiable_id_index
CREATE INDEX IF NOT EXISTS notifications_notifiable_type_notifiable_id_index
    ON public.notifications USING btree
    (notifiable_type COLLATE pg_catalog."default" ASC NULLS LAST, notifiable_id ASC NULLS LAST)
    TABLESPACE pg_default;