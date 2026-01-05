#!/bin/bash

# Script para hacer deploy a PWA Financial (proyecto correcto)
# Número de proyecto: 320966737636
# ID del proyecto: pwafinancial

echo "🚀 Desplegando a PWA Financial..."
echo "📦 Proyecto: pwafinancial"
echo "🔢 Número: 320966737636"
echo ""

# Cambiar al proyecto correcto
gcloud config set project pwafinancial

# Hacer el deploy
gcloud app deploy --quiet

echo ""
echo "✅ Deploy completado en: https://pwafinancial.uc.r.appspot.com"
echo ""
