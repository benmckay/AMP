CREATE TABLE IF NOT EXISTS public.documents
(
    id bigint NOT NULL DEFAULT nextval('documents_id_seq'::regclass),
    request_id bigint NOT NULL,
    filename character varying(255) COLLATE pg_catalog."default" NOT NULL,
    original_filename character varying(255) COLLATE pg_catalog."default" NOT NULL,
    file_path character varying(500) COLLATE pg_catalog."default" NOT NULL,
    file_size integer,
    mime_type character varying(100) COLLATE pg_catalog."default",
    uploaded_by bigint NOT NULL,
    created_at timestamp(0) without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT documents_pkey PRIMARY KEY (id),
    CONSTRAINT documents_request_id_foreign FOREIGN KEY (request_id)
        REFERENCES public.access_requests (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE CASCADE,
    CONSTRAINT documents_uploaded_by_foreign FOREIGN KEY (uploaded_by)
        REFERENCES public.users (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION
)

TABLESPACE pg_default;

ALTER TABLE public.documents
    OWNER to postgres;

-- Index: public.documents_request_id_index
CREATE INDEX IF NOT EXISTS documents_request_id_index
    ON public.documents USING btree
    (request_id ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.documents_uploaded_by_index
CREATE INDEX IF NOT EXISTS documents_uploaded_by_index
    ON public.documents USING btree
    (uploaded_by ASC NULLS LAST)
    TABLESPACE pg_default;