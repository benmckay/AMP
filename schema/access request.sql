CREATE TABLE IF NOT EXISTS public.access_requests
(
    id bigint NOT NULL DEFAULT nextval('access_requests_id_seq'::regclass),
    request_number character varying(50) COLLATE pg_catalog."default" NOT NULL,
    requester_id bigint NOT NULL,
    requester_department_id bigint,
    designated_approver_id bigint,
    template_id bigint NOT NULL,
    department_id bigint,
    system_id bigint NOT NULL DEFAULT 1,
    request_type character varying(255) COLLATE pg_catalog."default" NOT NULL,
    status character varying(255) COLLATE pg_catalog."default" NOT NULL DEFAULT 'pending'::character varying,
    payroll_number character varying(50) COLLATE pg_catalog."default",
    first_name character varying(100) COLLATE pg_catalog."default" NOT NULL,
    last_name character varying(100) COLLATE pg_catalog."default" NOT NULL,
    email character varying(255) COLLATE pg_catalog."default" NOT NULL,
    username character varying(100) COLLATE pg_catalog."default",
    job_title character varying(150) COLLATE pg_catalog."default",
    provider_group character varying(100) COLLATE pg_catalog."default",
    provider_type character varying(100) COLLATE pg_catalog."default",
    specialty character varying(100) COLLATE pg_catalog."default",
    service character varying(100) COLLATE pg_catalog."default",
    admitting boolean,
    ordering_physician boolean,
    sign_orders character varying(255) COLLATE pg_catalog."default",
    cosign_orders character varying(255) COLLATE pg_catalog."default",
    justification text COLLATE pg_catalog."default" NOT NULL,
    priority character varying(255) COLLATE pg_catalog."default" NOT NULL DEFAULT 'normal'::character varying,
    submitted_at timestamp(0) without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    approved_at timestamp(0) without time zone,
    approved_by bigint,
    approval_comments text COLLATE pg_catalog."default",
    fulfilled_at timestamp(0) without time zone,
    fulfilled_by bigint,
    fulfillment_notes text COLLATE pg_catalog."default",
    cancelled_at timestamp(0) without time zone,
    cancelled_by bigint,
    cancellation_reason text COLLATE pg_catalog."default",
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    is_user_trained boolean NOT NULL DEFAULT false,
    kpmdc character varying(100) COLLATE pg_catalog."default",
    cosigner boolean,
    CONSTRAINT access_requests_pkey PRIMARY KEY (id),
    CONSTRAINT access_requests_request_number_key UNIQUE (request_number),
    CONSTRAINT access_requests_approved_by_fkey FOREIGN KEY (approved_by)
        REFERENCES public.users (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE SET NULL,
    CONSTRAINT access_requests_cancelled_by_fkey FOREIGN KEY (cancelled_by)
        REFERENCES public.users (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE SET NULL,
    CONSTRAINT access_requests_department_id_fkey FOREIGN KEY (department_id)
        REFERENCES public.departments (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION,
    CONSTRAINT access_requests_designated_approver_id_fkey FOREIGN KEY (designated_approver_id)
        REFERENCES public.users (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE SET NULL,
    CONSTRAINT access_requests_fulfilled_by_fkey FOREIGN KEY (fulfilled_by)
        REFERENCES public.users (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE SET NULL,
    CONSTRAINT access_requests_requester_department_id_fkey FOREIGN KEY (requester_department_id)
        REFERENCES public.departments (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION,
    CONSTRAINT access_requests_requester_id_fkey FOREIGN KEY (requester_id)
        REFERENCES public.users (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION,
    CONSTRAINT access_requests_system_id_fkey FOREIGN KEY (system_id)
        REFERENCES public.systems (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION,
    CONSTRAINT access_requests_template_id_fkey FOREIGN KEY (template_id)
        REFERENCES public.templates (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION,
    CONSTRAINT access_requests_cosign_orders_check CHECK (cosign_orders::text = ANY (ARRAY['orders'::character varying, 'reports'::character varying, 'both'::character varying, 'neither'::character varying]::text[])),
    CONSTRAINT access_requests_priority_check CHECK (priority::text = ANY (ARRAY['low'::character varying, 'normal'::character varying, 'high'::character varying, 'urgent'::character varying]::text[])),
    CONSTRAINT access_requests_request_type_check CHECK (request_type::text = ANY (ARRAY['new_access'::character varying, 'additional_rights'::character varying, 'reactivation'::character varying, 'termination'::character varying]::text[])),
    CONSTRAINT access_requests_sign_orders_check CHECK (sign_orders::text = ANY (ARRAY['orders'::character varying, 'reports'::character varying, 'both'::character varying, 'neither'::character varying]::text[])),
    CONSTRAINT access_requests_status_check CHECK (status::text = ANY (ARRAY['pending'::character varying, 'approved'::character varying, 'rejected'::character varying, 'fulfilled'::character varying, 'cancelled'::character varying]::text[]))
)

TABLESPACE pg_default;

ALTER TABLE public.access_requests
    OWNER to postgres;

-- Index: public.access_requests_department_id_index
CREATE INDEX IF NOT EXISTS access_requests_department_id_index
    ON public.access_requests USING btree
    (department_id ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.access_requests_req_dept_status_submitted_idx
CREATE INDEX IF NOT EXISTS access_requests_req_dept_status_submitted_idx
    ON public.access_requests USING btree
    (requester_department_id ASC NULLS LAST, status COLLATE pg_catalog."default" ASC NULLS LAST, submitted_at ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.access_requests_request_number_index
CREATE INDEX IF NOT EXISTS access_requests_request_number_index
    ON public.access_requests USING btree
    (request_number COLLATE pg_catalog."default" ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.access_requests_requester_department_id_index
CREATE INDEX IF NOT EXISTS access_requests_requester_department_id_index
    ON public.access_requests USING btree
    (requester_department_id ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.access_requests_requester_id_index
CREATE INDEX IF NOT EXISTS access_requests_requester_id_index
    ON public.access_requests USING btree
    (requester_id ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.access_requests_requester_submitted_idx
CREATE INDEX IF NOT EXISTS access_requests_requester_submitted_idx
    ON public.access_requests USING btree
    (requester_id ASC NULLS LAST, submitted_at ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.access_requests_status_approved_at_idx
CREATE INDEX IF NOT EXISTS access_requests_status_approved_at_idx
    ON public.access_requests USING btree
    (status COLLATE pg_catalog."default" ASC NULLS LAST, approved_at ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.access_requests_status_department_id_index
CREATE INDEX IF NOT EXISTS access_requests_status_department_id_index
    ON public.access_requests USING btree
    (status COLLATE pg_catalog."default" ASC NULLS LAST, department_id ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.access_requests_status_fulfilled_at_idx
CREATE INDEX IF NOT EXISTS access_requests_status_fulfilled_at_idx
    ON public.access_requests USING btree
    (status COLLATE pg_catalog."default" ASC NULLS LAST, fulfilled_at ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.access_requests_status_index
CREATE INDEX IF NOT EXISTS access_requests_status_index
    ON public.access_requests USING btree
    (status COLLATE pg_catalog."default" ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.access_requests_submitted_at_index
CREATE INDEX IF NOT EXISTS access_requests_submitted_at_index
    ON public.access_requests USING btree
    (submitted_at ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.access_requests_template_id_index
CREATE INDEX IF NOT EXISTS access_requests_template_id_index
    ON public.access_requests USING btree
    (template_id ASC NULLS LAST)
    TABLESPACE pg_default;