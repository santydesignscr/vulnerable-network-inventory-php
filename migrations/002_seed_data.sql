-- ============================================================================
-- Seed Data for Net Inventory
-- ⚠️ Contains test data for security testing
-- ============================================================================

USE net_inventory;

-- ============================================================================
-- Device Types
-- ============================================================================
INSERT INTO device_types (slug, label) VALUES
('router','Router'),
('switch','Switch'),
('firewall','Firewall'),
('ap','Access Point'),
('server','Server');

-- ============================================================================
-- Vendors
-- ============================================================================
INSERT INTO vendors (name) VALUES 
('Cisco'),
('Juniper'),
('MikroTik'),
('Ubiquiti'),
('HP/Aruba'),
('Fortinet'),
('Palo Alto');

-- ============================================================================
-- Device Models
-- ============================================================================
INSERT INTO device_models (vendor_id, model_name, os_family, notes) VALUES
(1,'ISR4321','IOS-XE','Integrated Services Router'),
(1,'ISR4331','IOS-XE','Integrated Services Router'),
(1,'Catalyst 9300','IOS-XE','Enterprise Switch'),
(1,'Catalyst 2960-X','IOS','Access Switch'),
(1,'ASA 5516-X','ASA','Adaptive Security Appliance'),
(2,'MX104','JUNOS','Universal Routing Platform'),
(2,'EX4300','JUNOS','Ethernet Switch'),
(3,'RB4011','RouterOS','Router Board'),
(4,'UniFi AP-AC-Lite','UniFi','Access Point'),
(4,'UniFi Dream Machine','UniFi OS','All-in-One'),
(5,'Aruba 2930F','ArubaOS','Switch'),
(6,'FortiGate 60F','FortiOS','Next-Gen Firewall'),
(7,'PA-220','PAN-OS','Next-Gen Firewall');

-- ============================================================================
-- Locations
-- ============================================================================
INSERT INTO locations (name, parent_id, address, notes) VALUES
('Sede Central', NULL, 'Av. Principal 123, Ciudad', 'Oficina principal'),
('Datacenter 1', 1, 'Zona Industrial, Bloque A', 'Datacenter primario'),
('Datacenter 2', 1, 'Zona Industrial, Bloque B', 'Datacenter secundario'),
('Sucursal Norte', NULL, 'Calle Norte 456', 'Oficina regional norte'),
('Sucursal Sur', NULL, 'Av. Sur 789', 'Oficina regional sur'),
('Rack A1', 2, NULL, 'Rack principal DC1'),
('Rack A2', 2, NULL, 'Rack secundario DC1'),
('Sala Servidores', 1, NULL, 'Sala de servidores sede central');

-- ============================================================================
-- Users (Passwords in plain text for testing - VULNERABLE)
-- Password for all users: "password123"
-- Hash: $2y$10$YourHashHere (bcrypt) - In production use password_hash()
-- ============================================================================
INSERT INTO users (username, email, password_hash, role, full_name, is_active) VALUES
('admin', 'admin@netinv.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Administrator', 1),
('operator1', 'operator@netinv.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'operator', 'John Operator', 1),
('viewer1', 'viewer@netinv.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'viewer', 'Jane Viewer', 1),
('testuser', 'test@netinv.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'viewer', 'Test User', 1);

-- Note: Password "password123" hash = $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi

-- ============================================================================
-- Sample Devices
-- ============================================================================
INSERT INTO devices (hostname, management_ip, device_type_id, model_id, serial_number, ios_version, location_id, owner, purchase_date, warranty_until, notes)
VALUES
('rtr-core-01', INET6_ATON('10.0.0.1'), 1, 1, 'FTX2145A0B1', '16.9.4', 2, 'NetOps Team', '2021-06-15', '2024-06-15', 'Core router datacenter 1'),
('rtr-core-02', INET6_ATON('10.0.0.2'), 1, 2, 'FTX2145A0B2', '16.9.5', 3, 'NetOps Team', '2021-07-20', '2024-07-20', 'Core router datacenter 2'),
('sw-access-01', INET6_ATON('10.1.1.10'), 2, 3, 'FCW2201A123', '16.12.4', 6, 'IT Department', '2022-03-10', '2025-03-10', 'Access switch rack A1'),
('sw-access-02', INET6_ATON('10.1.1.11'), 2, 4, 'FOC2234Y5M9', '15.2(7)E3', 6, 'IT Department', '2020-11-22', '2023-11-22', 'Access switch rack A1'),
('fw-edge-01', INET6_ATON('192.168.100.1'), 3, 5, 'JAD214501AB', '9.14(2)', 2, 'Security Team', '2021-09-05', '2024-09-05', 'Edge firewall primary'),
('fw-edge-02', INET6_ATON('192.168.100.2'), 3, 12, 'FG60FTK20123456', '7.0.8', 3, 'Security Team', '2022-12-01', '2025-12-01', 'Edge firewall secondary'),
('rtr-branch-n1', INET6_ATON('172.16.10.1'), 1, 8, 'RB4011-SN123', '7.6', 4, 'Branch Operations', '2022-02-14', '2025-02-14', 'Branch north router'),
('ap-office-01', INET6_ATON('10.2.1.50'), 4, 9, 'UAP-AC-LITE-001', '5.43.23', 1, 'Wireless Team', '2021-04-18', '2024-04-18', 'Office AP floor 1'),
('ap-office-02', INET6_ATON('10.2.1.51'), 4, 9, 'UAP-AC-LITE-002', '5.43.23', 1, 'Wireless Team', '2021-04-18', '2024-04-18', 'Office AP floor 2'),
('sw-dist-01', INET6_ATON('10.0.1.1'), 2, 7, 'PEA123456789', '16.6.5', 2, 'NetOps Team', '2020-08-30', '2023-08-30', 'Distribution switch DC1');

-- ============================================================================
-- Sample Interfaces
-- ============================================================================
INSERT INTO interfaces (device_id, name, description, speed, admin_status, oper_status, ip_address, notes)
VALUES
(1, 'GigabitEthernet0/0/0', 'WAN Interface', '1000', 'up', 'up', INET6_ATON('10.0.0.1'), 'Primary uplink'),
(1, 'GigabitEthernet0/0/1', 'LAN Interface', '1000', 'up', 'up', INET6_ATON('192.168.1.1'), 'Internal network'),
(3, 'GigabitEthernet1/0/1', 'Uplink to Core', '10000', 'up', 'up', NULL, 'Trunk port'),
(3, 'GigabitEthernet1/0/2', 'Server VLAN', '1000', 'up', 'up', NULL, 'Access port'),
(5, 'Management0/0', 'Management', '1000', 'up', 'up', INET6_ATON('192.168.100.1'), 'MGMT interface'),
(5, 'GigabitEthernet0/0', 'Outside', '1000', 'up', 'up', NULL, 'External interface'),
(5, 'GigabitEthernet0/1', 'Inside', '1000', 'up', 'up', NULL, 'Internal interface');

-- ============================================================================
-- Sample Configurations
-- ============================================================================
INSERT INTO configs (device_id, filename, uploaded_by, content, notes)
VALUES
(1, 'rtr-core-01-running.cfg', 1, 'hostname rtr-core-01
!
interface GigabitEthernet0/0/0
 description WAN Interface
 ip address 10.0.0.1 255.255.255.0
 no shutdown
!
interface GigabitEthernet0/0/1
 description LAN Interface  
 ip address 192.168.1.1 255.255.255.0
 no shutdown
!
router ospf 1
 network 10.0.0.0 0.0.0.255 area 0
 network 192.168.1.0 0.0.0.255 area 0
!
line vty 0 4
 transport input ssh
!
end', 'Initial configuration backup'),

(3, 'sw-access-01-config.txt', 1, 'hostname sw-access-01
!
vlan 10
 name SERVERS
vlan 20
 name USERS
vlan 30
 name MGMT
!
interface GigabitEthernet1/0/1
 description Uplink to Core
 switchport mode trunk
 switchport trunk allowed vlan 10,20,30
!
interface range GigabitEthernet1/0/2-24
 switchport mode access
 switchport access vlan 20
 spanning-tree portfast
!
end', 'Production config 2023-10'),

(5, 'fw-edge-01.cfg', 2, 'hostname fw-edge-01
!
interface Management0/0
 nameif management
 security-level 100
 ip address 192.168.100.1 255.255.255.0
!
interface GigabitEthernet0/0
 nameif outside
 security-level 0
 ip address dhcp
!
interface GigabitEthernet0/1
 nameif inside
 security-level 100
 ip address 192.168.1.254 255.255.255.0
!
access-list OUTSIDE_IN extended permit tcp any host 192.168.1.10 eq 443
access-list OUTSIDE_IN extended permit icmp any any
!
access-group OUTSIDE_IN in interface outside
!', 'Firewall baseline config');

-- ============================================================================
-- Sample Change Log
-- ============================================================================
INSERT INTO change_log (device_id, user_id, action, details)
VALUES
(1, 1, 'create', '{"hostname": "rtr-core-01", "ip": "10.0.0.1"}'),
(1, 1, 'upload-config', '{"filename": "rtr-core-01-running.cfg", "size": 512}'),
(3, 2, 'update', '{"field": "ios_version", "old": "16.12.3", "new": "16.12.4"}'),
(5, 1, 'create', '{"hostname": "fw-edge-01", "type": "firewall"}'),
(7, 2, 'create', '{"hostname": "rtr-branch-n1", "location": "Sucursal Norte"}');

-- ============================================================================
-- Sample IP Assignments
-- ============================================================================
INSERT INTO ip_assignments (device_id, interface_id, ip, prefix, assigned_for)
VALUES
(1, 1, INET6_ATON('10.0.0.1'), 24, 'WAN Management'),
(1, 2, INET6_ATON('192.168.1.1'), 24, 'LAN Gateway'),
(5, 5, INET6_ATON('192.168.100.1'), 24, 'Firewall Management'),
(NULL, NULL, INET6_ATON('10.0.0.100'), 24, 'Reserved for future router'),
(NULL, NULL, INET6_ATON('192.168.50.1'), 24, 'Guest network gateway');

-- ============================================================================
-- End of seed data
-- ============================================================================
