CREATE TABLE IF NOT EXISTS public.failed_jobs
(
    id bigint NOT NULL DEFAULT nextval('failed_jobs_id_seq'::regclass),
    uuid character varying(255) COLLATE pg_catalog."default" NOT NULL,
    connection text COLLATE pg_catalog."default" NOT NULL,
    queue text COLLATE pg_catalog."default" NOT NULL,
    payload text COLLATE pg_catalog."default" NOT NULL,
    exception text COLLATE pg_catalog."default" NOT NULL,
    failed_at timestamp(0) without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT failed_jobs_pkey PRIMARY KEY (id),
    CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid)
)

TABLESPACE pg_default;

ALTER TABLE public.failed_jobs
    OWNER to postgres;
