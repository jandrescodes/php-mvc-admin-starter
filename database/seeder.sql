USE auth_base;

-- =========================================================================
-- ROLES
-- =========================================================================

INSERT INTO roles (name, description, status, is_system) VALUES
('Administrator', 'Full system access — bypasses all permission checks', 1, 1),
('Editor',        'Can manage users and view own profile',                1, 0),
('Auditor',       'Can view the activity log and own profile',            1, 0),
('Viewer',        'Read-only access to own profile',                      1, 0),
('Inactive Role', 'Example of a deactivated role',                       0, 0);

-- =========================================================================
-- PERMISSIONS
-- =========================================================================

INSERT INTO permissions (name, description, status) VALUES
('profile',          'Access to own profile and password changes', 1),
('admin',            'General administration (superuser)',         1),
('users',            'User management',                           1),
('permissions',      'Permission management',                     1),
('roles',            'Role management',                           1),
('audit_log',        'View the system audit/activity log',        1),
('audit_log_export', 'Export the audit log to PDF',               1);

-- =========================================================================
-- USERS  (password for all: admin123)
-- Hash: $2y$10$qot77JOYGGz5WTgp8TEor.jH3hwSOJ0fhu027oy5XbeM9P3RRnmni
--
-- created_at is spread across the last 6 months so the dashboard
-- line chart always shows meaningful data regardless of run date.
-- =========================================================================

-- Role IDs (fixed): 1=Administrator, 2=Editor, 3=Auditor, 4=Viewer, 5=Inactive Role
INSERT INTO users (
    name, first_surname, second_surname,
    document_type, document_number,
    address, phone, email, password,
    status, role_id, created_at
) VALUES
-- Month -6: founding admin
('Administrator', 'System',   NULL,
 'DNI', '12345678', 'Main office',          '900111111', 'admin@sistema.com',
 '$2y$10$qot77JOYGGz5WTgp8TEor.jH3hwSOJ0fhu027oy5XbeM9P3RRnmni',
 1, 1, DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 6 MONTH), '%Y-%m-01 08:00:00')),
-- Month -5
('Ana',      'Paredes',  'Molina',
 'DNI', '42345678', 'Av. Primavera 123',    '900222222', 'ana.paredes@sistema.com',
 '$2y$10$qot77JOYGGz5WTgp8TEor.jH3hwSOJ0fhu027oy5XbeM9P3RRnmni',
 1, 2, DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m-05 09:00:00')),
('Carlos',   'Rojas',    'Vega',
 'DNI', '52345678', 'Jr. Lima 456',         '900333333', 'carlos.rojas@sistema.com',
 '$2y$10$qot77JOYGGz5WTgp8TEor.jH3hwSOJ0fhu027oy5XbeM9P3RRnmni',
 1, 4, DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m-12 10:00:00')),
-- Month -4
('Lucia',    'Quispe',   'Salas',
 'CE',  'X1234567', 'Calle Norte 789',      '900444444', 'lucia.quispe@sistema.com',
 '$2y$10$qot77JOYGGz5WTgp8TEor.jH3hwSOJ0fhu027oy5XbeM9P3RRnmni',
 0, 4, DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 4 MONTH), '%Y-%m-03 11:00:00')),
('Diego',    'Torres',   'Luna',
 'PAS', 'PA998877', 'Av. Central 1001',     '900555555', 'diego.torres@sistema.com',
 '$2y$10$qot77JOYGGz5WTgp8TEor.jH3hwSOJ0fhu027oy5XbeM9P3RRnmni',
 1, 4, DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 4 MONTH), '%Y-%m-18 14:00:00')),
('Sofia',    'Mendoza',  'Cruz',
 'DNI', '61234567', 'Av. Los Pinos 234',    '900666666', 'sofia.mendoza@sistema.com',
 '$2y$10$qot77JOYGGz5WTgp8TEor.jH3hwSOJ0fhu027oy5XbeM9P3RRnmni',
 1, 3, DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 4 MONTH), '%Y-%m-22 15:30:00')),
-- Month -3
('Miguel',   'Flores',   'Ramos',
 'DNI', '71234567', 'Jr. Cusco 890',        '900777777', 'miguel.flores@sistema.com',
 '$2y$10$qot77JOYGGz5WTgp8TEor.jH3hwSOJ0fhu027oy5XbeM9P3RRnmni',
 1, 2, DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 3 MONTH), '%Y-%m-07 08:30:00')),
('Valeria',  'Castro',   NULL,
 'DNI', '81234567', 'Calle Sur 456',        '900888888', 'valeria.castro@sistema.com',
 '$2y$10$qot77JOYGGz5WTgp8TEor.jH3hwSOJ0fhu027oy5XbeM9P3RRnmni',
 1, 3, DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 3 MONTH), '%Y-%m-15 09:45:00')),
('Roberto',  'Herrera',  'Diaz',
 'CE',  'Y9876543', 'Av. Norte 321',        '900999999', 'roberto.herrera@sistema.com',
 '$2y$10$qot77JOYGGz5WTgp8TEor.jH3hwSOJ0fhu027oy5XbeM9P3RRnmni',
 0, 4, DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 3 MONTH), '%Y-%m-28 16:00:00')),
-- Month -2
('Elena',    'Vargas',   'Pinto',
 'DNI', '91234567', 'Jr. Arequipa 789',     '901000001', 'elena.vargas@sistema.com',
 '$2y$10$qot77JOYGGz5WTgp8TEor.jH3hwSOJ0fhu027oy5XbeM9P3RRnmni',
 1, 2, DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '%Y-%m-10 10:00:00')),
('Jorge',    'Aguirre',  'Soto',
 'DNI', '10234567', 'Calle Este 123',       '901000002', 'jorge.aguirre@sistema.com',
 '$2y$10$qot77JOYGGz5WTgp8TEor.jH3hwSOJ0fhu027oy5XbeM9P3RRnmni',
 1, 4, DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '%Y-%m-20 11:30:00')),
-- Month -1
('Patricia', 'Lara',     'Mora',
 'PAS', 'PB112233', 'Av. Oeste 456',        '901000003', 'patricia.lara@sistema.com',
 '$2y$10$qot77JOYGGz5WTgp8TEor.jH3hwSOJ0fhu027oy5XbeM9P3RRnmni',
 1, 3, DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-05 08:00:00')),
('Fernando', 'Rios',     NULL,
 'DNI', '11234567', 'Jr. Miraflores 890',   '901000004', 'fernando.rios@sistema.com',
 '$2y$10$qot77JOYGGz5WTgp8TEor.jH3hwSOJ0fhu027oy5XbeM9P3RRnmni',
 0, 4, DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-18 14:00:00')),
-- Current month
('Isabel',   'Navarro',  'Leon',
 'DNI', '12134567', 'Calle Central 234',    '901000005', 'isabel.navarro@sistema.com',
 '$2y$10$qot77JOYGGz5WTgp8TEor.jH3hwSOJ0fhu027oy5XbeM9P3RRnmni',
 1, 4, NOW());

-- =========================================================================
-- USER VARIABLES
-- =========================================================================

SET @admin_id    = (SELECT id FROM users WHERE email = 'admin@sistema.com'          LIMIT 1);
SET @ana_id      = (SELECT id FROM users WHERE email = 'ana.paredes@sistema.com'    LIMIT 1);
SET @carlos_id   = (SELECT id FROM users WHERE email = 'carlos.rojas@sistema.com'   LIMIT 1);
SET @lucia_id    = (SELECT id FROM users WHERE email = 'lucia.quispe@sistema.com'   LIMIT 1);
SET @diego_id    = (SELECT id FROM users WHERE email = 'diego.torres@sistema.com'   LIMIT 1);
SET @sofia_id    = (SELECT id FROM users WHERE email = 'sofia.mendoza@sistema.com'  LIMIT 1);
SET @miguel_id   = (SELECT id FROM users WHERE email = 'miguel.flores@sistema.com'  LIMIT 1);
SET @valeria_id  = (SELECT id FROM users WHERE email = 'valeria.castro@sistema.com' LIMIT 1);
SET @elena_id    = (SELECT id FROM users WHERE email = 'elena.vargas@sistema.com'   LIMIT 1);
SET @patricia_id = (SELECT id FROM users WHERE email = 'patricia.lara@sistema.com'  LIMIT 1);

-- =========================================================================
-- ROLE PERMISSIONS
-- (Administrator uses is_system=1 — no rows needed)
-- =========================================================================

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p
    ON r.name = 'Editor' AND p.name IN ('profile', 'users');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p
    ON r.name = 'Auditor' AND p.name IN ('profile', 'audit_log');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p
    ON r.name = 'Viewer' AND p.name IN ('profile');

-- =========================================================================
-- DIRECT USER PERMISSIONS (layer on top of role permissions — UNION model)
-- =========================================================================

-- Admin gets all permissions directly as well
INSERT INTO user_permissions (permission_id, user_id)
SELECT id, @admin_id FROM permissions;

-- Ana (Editor): also gets roles management directly
INSERT INTO user_permissions (permission_id, user_id)
SELECT p.id, @ana_id FROM permissions p WHERE p.name IN ('profile', 'users', 'roles');

-- Carlos, Lucia, Diego (Viewer): profile only
INSERT INTO user_permissions (permission_id, user_id)
SELECT p.id, @carlos_id FROM permissions p WHERE p.name IN ('profile');

INSERT INTO user_permissions (permission_id, user_id)
SELECT p.id, @lucia_id FROM permissions p WHERE p.name IN ('profile');

INSERT INTO user_permissions (permission_id, user_id)
SELECT p.id, @diego_id FROM permissions p WHERE p.name IN ('profile');

-- Sofia (Auditor): also gets permissions management + export directly
INSERT INTO user_permissions (permission_id, user_id)
SELECT p.id, @sofia_id FROM permissions p WHERE p.name IN ('profile', 'audit_log', 'permissions', 'audit_log_export');

-- Miguel, Elena (Editor): profile + users via role
INSERT INTO user_permissions (permission_id, user_id)
SELECT p.id, @miguel_id FROM permissions p WHERE p.name IN ('profile', 'users');

INSERT INTO user_permissions (permission_id, user_id)
SELECT p.id, @elena_id FROM permissions p WHERE p.name IN ('profile', 'users');

-- Valeria, Patricia (Auditor): profile + audit_log via role
INSERT INTO user_permissions (permission_id, user_id)
SELECT p.id, @valeria_id FROM permissions p WHERE p.name IN ('profile', 'audit_log');

INSERT INTO user_permissions (permission_id, user_id)
SELECT p.id, @patricia_id FROM permissions p WHERE p.name IN ('profile', 'audit_log');

-- =========================================================================
-- AUDIT LOG — entries spread across the last 6 months
-- =========================================================================

-- ---- Month -6: initial setup ----
SET @t = DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 6 MONTH), '%Y-%m-01 08:00:00');

INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator System', 'roles', 'create', 'Role created: Administrator',
 JSON_OBJECT('name','Administrator','is_system',1), '127.0.0.1', @t);
SET @t = ADDTIME(@t, '00:02:00');
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator System', 'roles', 'create', 'Role created: Editor',
 JSON_OBJECT('name','Editor','is_system',0), '127.0.0.1', @t);
SET @t = ADDTIME(@t, '00:02:00');
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator System', 'roles', 'create', 'Role created: Auditor',
 JSON_OBJECT('name','Auditor','is_system',0), '127.0.0.1', @t);
SET @t = ADDTIME(@t, '00:02:00');
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator System', 'roles', 'create', 'Role created: Viewer',
 JSON_OBJECT('name','Viewer','is_system',0), '127.0.0.1', @t);
SET @t = ADDTIME(@t, '00:02:00');
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator System', 'permissions', 'create', 'Permission created: profile',
 JSON_OBJECT('name','profile'), '127.0.0.1', @t);
SET @t = ADDTIME(@t, '00:02:00');
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator System', 'permissions', 'create', 'Permission created: users',
 JSON_OBJECT('name','users'), '127.0.0.1', @t);
SET @t = ADDTIME(@t, '00:02:00');
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator System', 'permissions', 'create', 'Permission created: roles',
 JSON_OBJECT('name','roles'), '127.0.0.1', @t);
SET @t = ADDTIME(@t, '00:02:00');
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator System', 'permissions', 'create', 'Permission created: permissions',
 JSON_OBJECT('name','permissions'), '127.0.0.1', @t);
SET @t = ADDTIME(@t, '00:02:00');
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator System', 'permissions', 'create', 'Permission created: audit_log',
 JSON_OBJECT('name','audit_log'), '127.0.0.1', @t);
SET @t = ADDTIME(@t, '00:02:00');
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator System', 'users', 'create', 'User created: Ana Paredes',
 JSON_OBJECT('email','ana.paredes@sistema.com','role','Editor','status','active'), '127.0.0.1', @t);
SET @t = ADDTIME(@t, '00:02:00');
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator System', 'users', 'create', 'User created: Carlos Rojas',
 JSON_OBJECT('email','carlos.rojas@sistema.com','role','Viewer','status','active'), '127.0.0.1', @t);
SET @t = ADDTIME(@t, '00:02:00');
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator System', 'roles', 'sync_permissions', 'Permissions synced for role: Editor',
 JSON_OBJECT('role','Editor','permissions','profile, users'), '127.0.0.1', @t);
SET @t = ADDTIME(@t, '00:02:00');
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, user_agent, created_at) VALUES
(@admin_id, 'Administrator System', 'auth', 'login', 'Successful login: Administrator System',
 JSON_OBJECT('email','admin@sistema.com'), '127.0.0.1', 'Mozilla/5.0', @t);

-- ---- Month -4: new users and Auditor role ----
SET @t = DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 4 MONTH), '%Y-%m-03 09:00:00');

INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator System', 'users', 'create', 'User created: Lucia Quispe',
 JSON_OBJECT('email','lucia.quispe@sistema.com','role','Viewer','status','inactive'), '127.0.0.1', @t);
SET @t = ADDTIME(@t, '00:05:00');
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator System', 'users', 'create', 'User created: Diego Torres',
 JSON_OBJECT('email','diego.torres@sistema.com','role','Viewer','status','active'), '127.0.0.1', @t);
SET @t = ADDTIME(@t, '00:05:00');
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator System', 'users', 'create', 'User created: Sofia Mendoza',
 JSON_OBJECT('email','sofia.mendoza@sistema.com','role','Auditor','status','active'), '127.0.0.1', @t);
SET @t = ADDTIME(@t, '00:05:00');
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator System', 'roles', 'sync_permissions', 'Permissions synced for role: Auditor',
 JSON_OBJECT('role','Auditor','permissions','profile, audit_log'), '127.0.0.1', @t);
SET @t = ADDTIME(@t, '02:00:00');
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, user_agent, created_at) VALUES
(@ana_id, 'Ana Paredes', 'auth', 'login', 'Successful login: Ana Paredes',
 JSON_OBJECT('email','ana.paredes@sistema.com'), '192.168.1.10', 'Mozilla/5.0', @t);
SET @t = ADDTIME(@t, '00:10:00');
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, user_agent, created_at) VALUES
(@ana_id, 'Ana Paredes', 'users', 'update', 'User updated: Carlos Rojas',
 JSON_OBJECT('user_id',@carlos_id,'field','phone'), '192.168.1.10', 'Mozilla/5.0', @t);

-- ---- Month -3: more users + deactivation ----
SET @t = DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 3 MONTH), '%Y-%m-07 10:00:00');

INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator System', 'users', 'create', 'User created: Miguel Flores',
 JSON_OBJECT('email','miguel.flores@sistema.com','role','Editor','status','active'), '127.0.0.1', @t);
SET @t = ADDTIME(@t, '00:05:00');
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator System', 'users', 'create', 'User created: Valeria Castro',
 JSON_OBJECT('email','valeria.castro@sistema.com','role','Auditor','status','active'), '127.0.0.1', @t);
SET @t = ADDTIME(@t, '00:05:00');
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator System', 'users', 'create', 'User created: Roberto Herrera',
 JSON_OBJECT('email','roberto.herrera@sistema.com','role','Viewer','status','inactive'), '127.0.0.1', @t);
SET @t = ADDTIME(@t, '00:05:00');
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, user_agent, created_at) VALUES
(@admin_id, 'Administrator System', 'users', 'deactivate', 'User deactivated: Lucia Quispe',
 JSON_OBJECT('user_id',@lucia_id), '127.0.0.1', 'Mozilla/5.0', @t);
SET @t = ADDTIME(@t, '01:30:00');
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, user_agent, created_at) VALUES
(@sofia_id, 'Sofia Mendoza', 'auth', 'login', 'Successful login: Sofia Mendoza',
 JSON_OBJECT('email','sofia.mendoza@sistema.com'), '192.168.1.20', 'Mozilla/5.0', @t);

-- ---- Month -2: permission changes ----
SET @t = DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '%Y-%m-10 09:00:00');

INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator System', 'users', 'create', 'User created: Elena Vargas',
 JSON_OBJECT('email','elena.vargas@sistema.com','role','Editor','status','active'), '127.0.0.1', @t);
SET @t = ADDTIME(@t, '00:05:00');
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator System', 'users', 'create', 'User created: Jorge Aguirre',
 JSON_OBJECT('email','jorge.aguirre@sistema.com','role','Viewer','status','active'), '127.0.0.1', @t);
SET @t = ADDTIME(@t, '00:05:00');
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator System', 'permissions', 'assign', 'Permission assigned to Sofia Mendoza: permissions',
 JSON_OBJECT('user_id',@sofia_id,'permission','permissions'), '127.0.0.1', @t);
SET @t = ADDTIME(@t, '00:05:00');
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator System', 'roles', 'update', 'Role updated: Editor',
 JSON_OBJECT('role_id',2,'description','Manage users and roles'), '127.0.0.1', @t);
SET @t = ADDTIME(@t, '01:30:00');
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, user_agent, created_at) VALUES
(@ana_id, 'Ana Paredes', 'auth', 'login', 'Successful login: Ana Paredes',
 JSON_OBJECT('email','ana.paredes@sistema.com'), '192.168.1.10', 'Mozilla/5.0', @t);
SET @t = ADDTIME(@t, '00:15:00');
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, user_agent, created_at) VALUES
(@ana_id, 'Ana Paredes', 'users', 'update', 'User updated: Diego Torres',
 JSON_OBJECT('user_id',@diego_id,'field','address'), '192.168.1.10', 'Mozilla/5.0', @t);

-- ---- Month -1: recent activity ----
SET @t = DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-05 08:30:00');

INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator System', 'users', 'create', 'User created: Patricia Lara',
 JSON_OBJECT('email','patricia.lara@sistema.com','role','Auditor','status','active'), '127.0.0.1', @t);
SET @t = ADDTIME(@t, '00:05:00');
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator System', 'users', 'create', 'User created: Fernando Rios',
 JSON_OBJECT('email','fernando.rios@sistema.com','role','Viewer','status','inactive'), '127.0.0.1', @t);
SET @t = ADDTIME(@t, '00:05:00');
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, user_agent, created_at) VALUES
(@admin_id, 'Administrator System', 'users', 'deactivate', 'User deactivated: Fernando Rios',
 JSON_OBJECT('user_id',13), '127.0.0.1', 'Mozilla/5.0', @t);
SET @t = ADDTIME(@t, '02:00:00');
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, user_agent, created_at) VALUES
(@miguel_id, 'Miguel Flores', 'auth', 'login', 'Successful login: Miguel Flores',
 JSON_OBJECT('email','miguel.flores@sistema.com'), '192.168.1.30', 'Mozilla/5.0', @t);
SET @t = ADDTIME(@t, '00:20:00');
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, user_agent, created_at) VALUES
(@miguel_id, 'Miguel Flores', 'users', 'update', 'User updated: Carlos Rojas',
 JSON_OBJECT('user_id',@carlos_id,'field','address'), '192.168.1.30', 'Mozilla/5.0', @t);
SET @t = ADDTIME(@t, '00:10:00');
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, user_agent, created_at) VALUES
(@miguel_id, 'Miguel Flores', 'auth', 'logout', 'User logged out: Miguel Flores',
 JSON_OBJECT('email','miguel.flores@sistema.com'), '192.168.1.30', 'Mozilla/5.0', @t);

-- ---- Current month: latest actions ----
SET @t = DATE_FORMAT(CURDATE(), '%Y-%m-01 09:00:00');

INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, created_at) VALUES
(@admin_id, 'Administrator System', 'users', 'create', 'User created: Isabel Navarro',
 JSON_OBJECT('email','isabel.navarro@sistema.com','role','Viewer','status','active'), '127.0.0.1', @t);
SET @t = ADDTIME(@t, '00:10:00');
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, user_agent, created_at) VALUES
(@admin_id, 'Administrator System', 'auth', 'login', 'Successful login: Administrator System',
 JSON_OBJECT('email','admin@sistema.com'), '127.0.0.1', 'Mozilla/5.0', @t);
SET @t = ADDTIME(@t, '00:05:00');
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, user_agent, created_at) VALUES
(@admin_id, 'Administrator System', 'users', 'unlock', 'User login unlocked: Carlos Rojas',
 JSON_OBJECT('user_id',@carlos_id), '127.0.0.1', 'Mozilla/5.0', @t);
SET @t = ADDTIME(@t, '01:00:00');
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, user_agent, created_at) VALUES
(@elena_id, 'Elena Vargas', 'auth', 'login', 'Successful login: Elena Vargas',
 JSON_OBJECT('email','elena.vargas@sistema.com'), '192.168.1.40', 'Mozilla/5.0', @t);
SET @t = ADDTIME(@t, '00:30:00');
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, user_agent, created_at) VALUES
(@elena_id, 'Elena Vargas', 'profile', 'avatar_changed', 'User changed their avatar',
 JSON_OBJECT('user_id',@elena_id), '192.168.1.40', 'Mozilla/5.0', @t);
SET @t = ADDTIME(@t, '00:05:00');
INSERT INTO activity_logs (actor_id, actor_label, module, action, description, details, ip_address, user_agent, created_at) VALUES
(@elena_id, 'Elena Vargas', 'auth', 'logout', 'User logged out: Elena Vargas',
 JSON_OBJECT('email','elena.vargas@sistema.com'), '192.168.1.40', 'Mozilla/5.0', @t);
