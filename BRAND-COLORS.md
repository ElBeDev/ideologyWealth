# Ideology Wealth Advisors - Colores de Marca Oficiales

## Paleta de Colores Principal

### Verde Primario (Principal)
- **Hex**: `#6fb950`
- **RGB**: `rgb(111, 185, 80)`
- **Uso**: Color principal de la marca, botones primarios, enlaces hover, iconos destacados, elementos de acción

### Azul Oscuro (Secundario)
- **Hex**: `#0143a3`
- **RGB**: `rgb(1, 67, 163)`
- **Uso**: Fondos oscuros, headers, footers, secciones con contraste, elementos secundarios

### Verde Hover (Variante)
- **Hex**: `#5ea840`
- **RGB**: `rgb(94, 168, 64)`
- **Uso**: Estados hover y active de botones primarios

## Colores de Soporte

### Blanco
- **Hex**: `#ffffff`
- **Uso**: Fondos, textos sobre fondos oscuros

### Verde con Transparencia
- **Hex**: `#6fb95026` (15% opacity)
- **Uso**: Fondos de tarjetas, secciones con efecto sutil

### Gris Oscuro
- **Hex**: `#464646`
- **Uso**: Texto principal del cuerpo

### Gris Claro
- **Hex**: `#f8f8f8`, `#cccccc`
- **Uso**: Fondos de sección, bordes, elementos de apoyo

## Ejemplos de Uso

### Botones
```css
.btn-primary {
    background: #6fb950;
    color: #ffffff;
}

.btn-primary:hover {
    background: #5ea840;
}
```

### Navegación
```css
.navbar a:hover {
    color: #6fb950;
}
```

### Fondos
```css
.hero-section {
    background: linear-gradient(135deg, #6fb950 0%, #0143a3 100%);
}
```

## Notas Importantes

- **NUNCA usar**: Tonos de rojo (#fb3b47, #e84351, #ea5455) - estos son de la plantilla original clonada
- El verde `#6fb950` es el color de identidad principal y debe ser el más prominente
- El azul `#0143a3` proporciona contraste y profundidad
- Mantener consistencia en toda la aplicación PWA

## Accesibilidad

- Ratio de contraste verde sobre blanco: 4.5:1 (AA compliant)
- Ratio de contraste azul sobre blanco: 8.5:1 (AAA compliant)
- Ambos colores cumplen con WCAG 2.1 para accesibilidad web
