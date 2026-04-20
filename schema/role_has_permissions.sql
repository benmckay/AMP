CREATE TABLE IF NOT EXISTS public.role_has_permissions
(
    permission_id bigint NOT NULL,
    role_id bigint NOT NULL,
    CONSTRAINT role_has_permissions_pkey PRIMARY KEY (permission_id, role_id),
    CONSTRAINT role_has_permissions_permission_id_foreign FOREIGN KEY (permission_id)
        REFERENCES public.permissions (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE CASCADE,
    CONSTRAINT role_has_permissions_role_id_foreign FOREIGN KEY (role_id)
        REFERENCES public.roles (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE CASCADE
)

TABLESPACE pg_default;

ALTER TABLE public.role_has_permissions
    OWNER to postgres;
