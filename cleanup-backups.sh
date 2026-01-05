#!/bin/bash

# Script para limpiar archivos backup después de confirmar cambios
# IMPORTANTE: Solo ejecutar después de verificar que todo funciona correctamente

echo "🧹 Limpieza de Archivos Backup"
echo "================================"
echo ""
echo "⚠️  ADVERTENCIA: Este script eliminará todos los archivos .backup"
echo ""

# Contar archivos backup
backup_count=$(find . -name "*.css.backup" -type f | wc -l | xargs)

echo "📁 Archivos backup encontrados: $backup_count"
echo ""

if [ "$backup_count" -eq 0 ]; then
    echo "✅ No hay archivos backup para eliminar"
    exit 0
fi

echo "Los siguientes archivos serán eliminados:"
find . -name "*.css.backup" -type f | head -10
if [ "$backup_count" -gt 10 ]; then
    echo "... y $((backup_count - 10)) archivos más"
fi
echo ""

read -p "¿Estás seguro de que quieres eliminar los archivos backup? (si/no): " confirm

if [ "$confirm" = "si" ] || [ "$confirm" = "SI" ] || [ "$confirm" = "s" ] || [ "$confirm" = "S" ]; then
    echo ""
    echo "🗑️  Eliminando archivos backup..."
    find . -name "*.css.backup" -type f -delete
    echo "✅ Archivos backup eliminados correctamente"
    echo ""
    echo "💡 Consejo: Si necesitas revertir cambios en el futuro,"
    echo "   usa git para restaurar versiones anteriores"
else
    echo ""
    echo "❌ Operación cancelada. Los archivos backup se mantienen."
fi

echo ""
