#!/bin/bash
echo "🧪 INICIANDO TEST COMPLETO DE COOPERATIVA 🧪"
echo "-------------------------------------------"

COOKIE_PATH="/var/www/html/Backend/test/cookie.txt"
BASE_URL="http://localhost/Backend/api"

# 1️⃣ LOGIN
echo "🔹 Probando LOGIN..."
curl -s -c $COOKIE_PATH -d "login=1&email=carlosyrami@gmail.com&password=password12" -X POST "$BASE_URL/auth.php"
echo -e "\n✅ Login completado.\n"

# 2️⃣ LISTAR ASPIRANTES
echo "🔹 Listando aspirantes..."
curl -s -b $COOKIE_PATH "$BASE_URL/usuarios.php?accion=aspirantes"
echo -e "\n✅ Aspirantes listados.\n"

# 3️⃣ LISTAR SOCIOS
echo "🔹 Listando socios..."
curl -s -b $COOKIE_PATH "$BASE_URL/usuarios.php?accion=socios"
echo -e "\n✅ Socios listados.\n"

# 4️⃣ LISTAR UNIDADES
echo "🔹 Listando unidades habitacionales..."
curl -s -b $COOKIE_PATH "$BASE_URL/cooperativa.php?accion=unidades"
echo -e "\n✅ Unidades listadas.\n"

# 5️⃣ LISTAR COMPROBANTES
echo "🔹 Listando comprobantes..."
curl -s -b $COOKIE_PATH "$BASE_URL/cooperativa.php?accion=comprobantes"
echo -e "\n✅ Comprobantes listados.\n"

# 6️⃣ LISTAR HORAS DE TRABAJO
echo "🔹 Listando horas de trabajo..."
curl -s -b $COOKIE_PATH "$BASE_URL/cooperativa.php?accion=horas_trabajo"
echo -e "\n✅ Horas listadas.\n"

# 7️⃣ LOGOUT
echo "🔹 Cerrando sesión..."
php /var/www/html/Backend/logout.php
echo -e "✅ Logout completado.\n"

echo "-------------------------------------------"
echo "🎯 TEST COMPLETO FINALIZADO 🎯"
