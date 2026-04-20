CREATE TABLE IF NOT EXISTS public.job_batches
(
    id character varying(255) COLLATE pg_catalog."default" NOT NULL,
    name character varying(255) COLLATE pg_catalog."default" NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text COLLATE pg_catalog."default" NOT NULL,
    options text COLLATE pg_catalog."default",
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer,
    CONSTRAINT job_batches_pkey PRIMARY KEY (id)
)

TABLESPACE pg_default;

ALTER TABLE public.job_batches
    OWNER to postgres;
