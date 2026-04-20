CREATE TABLE IF NOT EXISTS public.request_approvals
(
    id bigint NOT NULL DEFAULT nextval('request_approvals_id_seq'::regclass),
    request_id bigint NOT NULL,
    approver_id bigint NOT NULL,
    action character varying(255) COLLATE pg_catalog."default" NOT NULL,
    status character varying(255) COLLATE pg_catalog."default" NOT NULL,
    comments text COLLATE pg_catalog."default",
    created_at timestamp(0) without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT request_approvals_pkey PRIMARY KEY (id),
    CONSTRAINT request_approvals_approver_id_foreign FOREIGN KEY (approver_id)
        REFERENCES public.users (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION,
    CONSTRAINT request_approvals_request_id_foreign FOREIGN KEY (request_id)
        REFERENCES public.access_requests (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE CASCADE,
    CONSTRAINT request_approvals_action_check CHECK (action::text = ANY (ARRAY['approved'::character varying, 'rejected'::character varying, 'returned'::character varying]::text[]))
)

TABLESPACE pg_default;

ALTER TABLE public.request_approvals
    OWNER to postgres;

-- Index: public.request_approvals_action_index
CREATE INDEX IF NOT EXISTS request_approvals_action_index
    ON public.request_approvals USING btree
    (action COLLATE pg_catalog."default" ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.request_approvals_approver_id_index
CREATE INDEX IF NOT EXISTS request_approvals_approver_id_index
    ON public.request_approvals USING btree
    (approver_id ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.request_approvals_request_id_index
CREATE INDEX IF NOT EXISTS request_approvals_request_id_index
    ON public.request_approvals USING btree
    (request_id ASC NULLS LAST)
    TABLESPACE pg_default;