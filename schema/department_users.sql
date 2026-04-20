CREATE TABLE IF NOT EXISTS public.department_users
(
    id bigint NOT NULL DEFAULT nextval('department_users_id_seq'::regclass),
    user_id bigint NOT NULL,
    department_id bigint NOT NULL,
    role character varying(255) COLLATE pg_catalog."default" NOT NULL,
    is_active boolean NOT NULL DEFAULT true,
    assigned_by bigint,
    assigned_at timestamp(0) without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT department_users_pkey PRIMARY KEY (id),
    CONSTRAINT department_users_user_id_department_id_unique UNIQUE (user_id, department_id),
    CONSTRAINT department_users_assigned_by_foreign FOREIGN KEY (assigned_by)
        REFERENCES public.users (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE SET NULL,
    CONSTRAINT department_users_department_id_foreign FOREIGN KEY (department_id)
        REFERENCES public.departments (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE CASCADE,
    CONSTRAINT department_users_user_id_foreign FOREIGN KEY (user_id)
        REFERENCES public.users (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE CASCADE,
    CONSTRAINT department_users_role_check CHECK (role::text = ANY (ARRAY['requester'::character varying, 'approver'::character varying, 'both'::character varying]::text[]))
)

TABLESPACE pg_default;

ALTER TABLE public.department_users
    OWNER to postgres;

-- Index: public.department_users_department_id_index
CREATE INDEX IF NOT EXISTS department_users_department_id_index
    ON public.department_users USING btree
    (department_id ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.department_users_department_role_active_idx
CREATE INDEX IF NOT EXISTS department_users_department_role_active_idx
    ON public.department_users USING btree
    (department_id ASC NULLS LAST, role COLLATE pg_catalog."default" ASC NULLS LAST, is_active ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.department_users_is_active_index
CREATE INDEX IF NOT EXISTS department_users_is_active_index
    ON public.department_users USING btree
    (is_active ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.department_users_role_index
CREATE INDEX IF NOT EXISTS department_users_role_index
    ON public.department_users USING btree
    (role COLLATE pg_catalog."default" ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.department_users_user_id_index
CREATE INDEX IF NOT EXISTS department_users_user_id_index
    ON public.department_users USING btree
    (user_id ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: public.department_users_user_role_active_idx
CREATE INDEX IF NOT EXISTS department_users_user_role_active_idx
    ON public.department_users USING btree
    (user_id ASC NULLS LAST, role COLLATE pg_catalog."default" ASC NULLS LAST, is_active ASC NULLS LAST)
    TABLESPACE pg_default;