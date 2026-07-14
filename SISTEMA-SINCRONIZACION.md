# 🔄 Sistema de Sincronización Admin ↔ Frontend
## Ideology Wealth Advisors - Flujo Completo de Datos

---

## 📊 DEPOSITS (Depósitos)

### **Flujo del Usuario (Frontend)**

1. **Usuario solicita depósito** (`deposits.html`)
   - Ingresa monto: ejemplo $100
   - Selecciona método: Bank Transfer, PayPal, Crypto, etc.
   - Se calcula cargo automático: 2% = $2.00
   - Total a recibir: $102.00

2. **Se crea el registro** (API: `/api/deposits.php`)
   ```sql
   INSERT INTO deposits (
     user_id, trx, gateway, amount, charge, 
     final_amount, status, created_at
   ) VALUES (
     123, 'DEP1234567890', 'Bank Transfer', 
     100.00, 2.00, 102.00, 'pending', NOW()
   )
   ```
   - Status inicial: `pending`
   - Balance del usuario: **NO CAMBIA** hasta que admin apruebe

3. **Usuario ve su depósito** (`deposits.html`)
   - Estado: `Pending` (amarillo)
   - Se muestra en la tabla de deposits del usuario

---

### **Flujo del Admin (Backend)**

4. **Admin revisa deposits** (`admin/deposits.html`)
   - Ve todos los deposits pendientes
   - Filtra por: All / Pending / Approved / Rejected

5. **Admin APRUEBA el depósito**
   ```javascript
   // Botón: Approve
   POST /api/admin_deposits.php
   {
     "action": "approve",
     "id": 456
   }
   ```

6. **Sistema actualiza automáticamente** (PHP: `/api/admin_deposits.php`)
   ```sql
   -- Actualiza estado del depósito
   UPDATE deposits 
   SET status = 'approved', updated_at = NOW() 
   WHERE id = 456;
   
   -- SUMA el dinero al balance del usuario
   UPDATE users 
   SET balance = balance + 102.00 
   WHERE id = 123;
   ```

7. **Usuario ve cambios INMEDIATAMENTE**
   - En `dashboard.html`: Balance aumenta de $0 a $102.00
   - En `deposits.html`: Depósito muestra status `Approved` (verde)

---

### **Si Admin RECHAZA el depósito**

```javascript
POST /api/admin_deposits.php
{
  "action": "reject",
  "id": 456
}
```

```sql
-- Solo actualiza estado, NO toca el balance
UPDATE deposits 
SET status = 'rejected', updated_at = NOW() 
WHERE id = 456;
```

Usuario ve: Status cambia a `Rejected` (rojo), balance sin cambios.

---

## 💸 WITHDRAWALS (Retiros/Transfers)

### **Flujo del Usuario (Frontend)**

1. **Usuario solicita retiro** (`withdrawals.html`)
   - Balance actual: $102.00
   - Quiere retirar: $50.00
   - Cargo 2%: $1.00
   - Total a descontar: $51.00

2. **Sistema valida balance** (API: `/api/withdrawals.php`)
   ```php
   if ($user['balance'] < $final_amount) {
     return error('Insufficient balance');
   }
   ```

3. **Se crea el retiro Y se descuenta INMEDIATAMENTE**
   ```sql
   -- Crea registro
   INSERT INTO withdrawals (
     user_id, trx, gateway, amount, charge,
     final_amount, status, created_at
   ) VALUES (
     123, 'WTH9876543210', 'Bank Transfer',
     50.00, 1.00, 51.00, 'pending', NOW()
   );
   
   -- RESTA el dinero del balance INMEDIATAMENTE
   UPDATE users 
   SET balance = balance - 51.00 
   WHERE id = 123;
   ```
   - Balance usuario: $102.00 → $51.00 ✅
   - Status: `pending` (esperando aprobación admin)

---

### **Flujo del Admin (Backend)**

4. **Admin revisa withdrawals** (`admin/withdrawals.html`)
   - Ve todos los retiros pendientes
   - Verifica datos bancarios/cuenta del usuario

5. **Admin APRUEBA el retiro**
   ```javascript
   POST /api/admin_withdrawals.php
   {
     "action": "approve",
     "id": 789
   }
   ```

6. **Sistema actualiza status** (PHP: `/api/admin_withdrawals.php`)
   ```sql
   -- Solo cambia estado (dinero ya fue restado)
   UPDATE withdrawals 
   SET status = 'approved', updated_at = NOW() 
   WHERE id = 789;
   ```
   - Balance usuario: $51.00 (sin cambios)
   - Status: `approved` ✅

---

### **Si Admin RECHAZA el retiro**

```javascript
POST /api/admin_withdrawals.php
{
  "action": "reject",
  "id": 789
}
```

```sql
-- Actualiza estado
UPDATE withdrawals 
SET status = 'rejected', updated_at = NOW() 
WHERE id = 789;

-- DEVUELVE el dinero al usuario
UPDATE users 
SET balance = balance + 51.00 
WHERE id = 123;
```

Usuario recupera: $51.00 → $102.00 ✅

---

## 🔍 VISUALIZACIÓN EN DASHBOARD

### **Dashboard del Usuario** (`dashboard.html`)

```javascript
// Carga datos del usuario
$.get('/api/user.php').done(response => {
  const user = response.user;
  
  // Muestra balance actualizado
  $('#availableBalance').text('$' + user.balance);
  
  // Muestra investment balance
  $('#investmentBalance').text('$' + user.investment_balance);
});
```

**El balance se actualiza automáticamente cuando:**
- ✅ Admin aprueba un deposit → Balance SUBE
- ✅ Usuario solicita withdrawal → Balance BAJA
- ✅ Admin rechaza withdrawal → Balance SUBE (devolución)

---

## 📁 Archivos del Sistema

### **API Backend (PHP)**
```
/api/
├── deposits.php           → Usuario crea deposits, ve sus deposits
├── admin_deposits.php     → Admin aprueba/rechaza deposits
├── withdrawals.php        → Usuario crea withdrawals, ve sus withdrawals
├── admin_withdrawals.php  → Admin aprueba/rechaza withdrawals
├── user.php              → Obtiene datos del usuario (balance, info)
└── config.php            → Conexión a base de datos
```

### **Frontend Usuario**
```
/
├── dashboard.html         → Dashboard con balance y gráficas
├── deposits.html          → Historial de depósitos del usuario
├── withdrawals.html       → Historial de retiros del usuario
└── profile.html           → Perfil y configuración
```

### **Panel Admin**
```
/admin/
├── dashboard.html         → Dashboard admin con estadísticas
├── deposits.html          → Gestión de deposits (aprobar/rechazar)
├── withdrawals.html       → Gestión de withdrawals (aprobar/rechazar)
└── users.html            → Gestión de usuarios
```

---

## 🗄️ Estructura de Base de Datos

### **Tabla: users**
```sql
CREATE TABLE users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(255) UNIQUE,
  email VARCHAR(255) UNIQUE,
  password VARCHAR(255),
  firstname VARCHAR(100),
  lastname VARCHAR(100),
  balance DECIMAL(15,2) DEFAULT 0.00,      -- Balance principal
  investment_balance DECIMAL(15,2) DEFAULT 0.00,
  account_number VARCHAR(50),
  country_code VARCHAR(10),
  mobile VARCHAR(20),
  status ENUM('active', 'banned') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### **Tabla: deposits**
```sql
CREATE TABLE deposits (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT,
  trx VARCHAR(50) UNIQUE,               -- Transaction ID (DEP1234567890)
  gateway VARCHAR(100),                 -- Bank Transfer, PayPal, etc.
  amount DECIMAL(15,2),                 -- Monto solicitado
  charge DECIMAL(15,2),                 -- Cargo 2%
  final_amount DECIMAL(15,2),           -- Total (amount + charge)
  status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  transaction_id VARCHAR(255),          -- ID externo (referencia)
  notes TEXT,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### **Tabla: withdrawals**
```sql
CREATE TABLE withdrawals (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT,
  trx VARCHAR(50) UNIQUE,               -- Transaction ID (WTH9876543210)
  gateway VARCHAR(100),                 -- Bank Transfer, PayPal, etc.
  amount DECIMAL(15,2),                 -- Monto a retirar
  charge DECIMAL(15,2),                 -- Cargo 2%
  final_amount DECIMAL(15,2),           -- Total (amount + charge)
  status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  account_details TEXT,                 -- Datos bancarios del usuario
  notes TEXT,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);
```

---

## 🔐 Seguridad y Validaciones

### **Validaciones en Deposits**
- ✅ Mínimo: $1.00
- ✅ Usuario autenticado (session)
- ✅ Cargo automático: 2%
- ✅ Admin debe aprobar antes de acreditar balance

### **Validaciones en Withdrawals**
- ✅ Mínimo: $10.00
- ✅ Usuario autenticado (session)
- ✅ Verificar balance suficiente
- ✅ Cargo automático: 2%
- ✅ Descuento inmediato del balance
- ✅ Devolución automática si admin rechaza

### **Seguridad Admin**
- ✅ Login separado: `admin/login.html`
- ✅ Session: `admin_logged_in`
- ✅ Transacciones con `beginTransaction()` y `commit()`
- ✅ Rollback automático en caso de error

---

## 🎯 Resumen del Flujo Completo

### **DEPOSITS**
```
Usuario solicita → pending → Admin aprueba → approved + Balance SUBE ✅
Usuario solicita → pending → Admin rechaza → rejected (sin cambios) ❌
```

### **WITHDRAWALS**
```
Usuario solicita → pending + Balance BAJA → Admin aprueba → approved ✅
Usuario solicita → pending + Balance BAJA → Admin rechaza → rejected + Balance SUBE (devolución) ❌
```

---

## ✅ TODO ESTÁ SINCRONIZADO

El sistema actual ya tiene **sincronización completa** entre admin y frontend:

1. ✅ Deposits aprobados suman balance
2. ✅ Deposits rechazados no afectan balance
3. ✅ Withdrawals restan balance al crear
4. ✅ Withdrawals rechazados devuelven balance
5. ✅ Dashboard muestra balance actualizado en tiempo real
6. ✅ Tablas de deposits/withdrawals muestran status correcto
7. ✅ Transacciones con rollback para integridad de datos

**El sistema está COMPLETO y FUNCIONAL** 🚀
