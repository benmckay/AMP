CREATE TABLE IF NOT EXISTS public.jobs
(
    id bigint NOT NULL DEFAULT nextval('jobs_id_seq'::regclass),
    queue character varying(255) COLLATE pg_catalog."default" NOT NULL,
    payload text COLLATE pg_catalog."default" NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL,
    CONSTRAINT jobs_pkey PRIMARY KEY (id)
)

TABLESPACE pg_default;

ALTER TABLE public.jobs
    OWNER to postgres;

-- Index: public.jobs_queue_index
CREATE INDEX IF NOT EXISTS jobs_queue_index
    ON public.jobs USING btree
    (queue COLLATE pg_catalog."default" ASC NULLS LAST)
    TABLESPACE pg_default;