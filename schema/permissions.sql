CREATE TABLE IF NOT EXISTS public.permissions
(
    id bigint NOT NULL DEFAULT nextval('permissions_id_seq'::regclass),
    name character varying(255) COLLATE pg_catalog."default" NOT NULL,
    guard_name character varying(255) COLLATE pg_catalog."default" NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT permissions_pkey PRIMARY KEY (id),
    CONSTRAINT permissions_name_guard_name_unique UNIQUE (name, guard_name)
)

TABLESPACE pg_default;

ALTER TABLE public.permissions
    OWNER to postgres;
