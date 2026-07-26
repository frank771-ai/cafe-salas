from __future__ import annotations

from pathlib import Path

from docx import Document
from docx.enum.section import WD_SECTION
from docx.enum.table import WD_CELL_VERTICAL_ALIGNMENT, WD_TABLE_ALIGNMENT
from docx.enum.text import WD_ALIGN_PARAGRAPH, WD_BREAK, WD_LINE_SPACING
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from docx.shared import Inches, Pt, RGBColor
from PIL import Image, ImageDraw, ImageFont


ROOT = Path(__file__).resolve().parents[1]
OUTPUT = ROOT / "docs" / "Documentacion_Cafe_Salas.docx"
ASSET_DIR = ROOT / "tmp" / "docx" / "final_assets"

FOREST = RGBColor(31, 104, 71)
DARK = RGBColor(23, 37, 29)
MUTED = RGBColor(96, 112, 103)
RUST = RGBColor(199, 102, 63)
LIGHT_FILL = "E8EEF5"
GREEN_FILL = "E7F0E9"
WHITE = RGBColor(255, 255, 255)


def set_run_font(run, name: str = "Calibri", size: float | None = None,
                 color: RGBColor | None = None, bold: bool | None = None,
                 italic: bool | None = None) -> None:
    run.font.name = name
    run._element.get_or_add_rPr().rFonts.set(qn("w:ascii"), name)
    run._element.get_or_add_rPr().rFonts.set(qn("w:hAnsi"), name)
    if size is not None:
        run.font.size = Pt(size)
    if color is not None:
        run.font.color.rgb = color
    if bold is not None:
        run.bold = bold
    if italic is not None:
        run.italic = italic


def configure_styles(document: Document) -> None:
    """Apply compact_reference_guide tokens with one consistent green brand override."""
    styles = document.styles
    normal = styles["Normal"]
    normal.font.name = "Calibri"
    normal._element.rPr.rFonts.set(qn("w:ascii"), "Calibri")
    normal._element.rPr.rFonts.set(qn("w:hAnsi"), "Calibri")
    normal.font.size = Pt(11)
    normal.font.color.rgb = DARK
    normal.paragraph_format.space_after = Pt(6)
    normal.paragraph_format.line_spacing = 1.25

    heading_tokens = {
        "Heading 1": (16, 18, 10),
        "Heading 2": (13, 14, 7),
        "Heading 3": (12, 10, 5),
    }
    for name, (size, before, after) in heading_tokens.items():
        style = styles[name]
        style.font.name = "Calibri"
        style._element.rPr.rFonts.set(qn("w:ascii"), "Calibri")
        style._element.rPr.rFonts.set(qn("w:hAnsi"), "Calibri")
        style.font.size = Pt(size)
        style.font.bold = True
        style.font.color.rgb = FOREST
        style.paragraph_format.space_before = Pt(before)
        style.paragraph_format.space_after = Pt(after)
        style.paragraph_format.keep_with_next = True

    for name in ("List Bullet", "List Number"):
        style = styles[name]
        style.font.name = "Calibri"
        style.font.size = Pt(11)
        style.paragraph_format.left_indent = Inches(0.375)
        style.paragraph_format.first_line_indent = Inches(-0.188)
        style.paragraph_format.space_after = Pt(4)
        style.paragraph_format.line_spacing = 1.25


def configure_page(document: Document) -> None:
    for section in document.sections:
        section.page_width = Inches(8.5)
        section.page_height = Inches(11)
        section.top_margin = Inches(1)
        section.right_margin = Inches(1)
        section.bottom_margin = Inches(1)
        section.left_margin = Inches(1)
        section.header_distance = Inches(0.492)
        section.footer_distance = Inches(0.492)


def add_page_field(paragraph) -> None:
    run = paragraph.add_run()
    begin = OxmlElement("w:fldChar")
    begin.set(qn("w:fldCharType"), "begin")
    instruction = OxmlElement("w:instrText")
    instruction.set(qn("xml:space"), "preserve")
    instruction.text = " PAGE "
    end = OxmlElement("w:fldChar")
    end.set(qn("w:fldCharType"), "end")
    run._r.extend([begin, instruction, end])


def configure_header_footer(document: Document) -> None:
    for section in document.sections:
        header = section.header.paragraphs[0]
        header.text = "CAFÉ SALAS  |  Proyecto Final ITI-523"
        header.alignment = WD_ALIGN_PARAGRAPH.RIGHT
        set_run_font(header.runs[0], size=8.5, color=MUTED, bold=True)

        footer = section.footer.paragraphs[0]
        footer.alignment = WD_ALIGN_PARAGRAPH.RIGHT
        run = footer.add_run("Pagina ")
        set_run_font(run, size=8.5, color=MUTED)
        add_page_field(footer)


def set_cell_shading(cell, fill: str) -> None:
    properties = cell._tc.get_or_add_tcPr()
    shading = properties.find(qn("w:shd"))
    if shading is None:
        shading = OxmlElement("w:shd")
        properties.append(shading)
    shading.set(qn("w:fill"), fill)


def set_cell_margins(cell, top: int = 80, start: int = 120,
                     bottom: int = 80, end: int = 120) -> None:
    properties = cell._tc.get_or_add_tcPr()
    margins = properties.first_child_found_in("w:tcMar")
    if margins is None:
        margins = OxmlElement("w:tcMar")
        properties.append(margins)
    for margin_name, value in (("top", top), ("start", start), ("bottom", bottom), ("end", end)):
        node = margins.find(qn(f"w:{margin_name}"))
        if node is None:
            node = OxmlElement(f"w:{margin_name}")
            margins.append(node)
        node.set(qn("w:w"), str(value))
        node.set(qn("w:type"), "dxa")


def set_table_geometry(table, widths_dxa: list[int], indent_dxa: int = 120) -> None:
    table.autofit = False
    table.alignment = WD_TABLE_ALIGNMENT.LEFT
    properties = table._tbl.tblPr
    table_width = properties.find(qn("w:tblW"))
    if table_width is None:
        table_width = OxmlElement("w:tblW")
        properties.append(table_width)
    table_width.set(qn("w:w"), str(sum(widths_dxa)))
    table_width.set(qn("w:type"), "dxa")

    table_indent = properties.find(qn("w:tblInd"))
    if table_indent is None:
        table_indent = OxmlElement("w:tblInd")
        properties.append(table_indent)
    table_indent.set(qn("w:w"), str(indent_dxa))
    table_indent.set(qn("w:type"), "dxa")

    grid = table._tbl.tblGrid
    for child in list(grid):
        grid.remove(child)
    for width in widths_dxa:
        column = OxmlElement("w:gridCol")
        column.set(qn("w:w"), str(width))
        grid.append(column)

    for row in table.rows:
        for index, cell in enumerate(row.cells):
            width = widths_dxa[min(index, len(widths_dxa) - 1)]
            cell.width = Inches(width / 1440)
            properties = cell._tc.get_or_add_tcPr()
            cell_width = properties.find(qn("w:tcW"))
            if cell_width is None:
                cell_width = OxmlElement("w:tcW")
                properties.append(cell_width)
            cell_width.set(qn("w:w"), str(width))
            cell_width.set(qn("w:type"), "dxa")
            cell.vertical_alignment = WD_CELL_VERTICAL_ALIGNMENT.CENTER
            set_cell_margins(cell)


def repeat_header(row) -> None:
    properties = row._tr.get_or_add_trPr()
    repeat = OxmlElement("w:tblHeader")
    repeat.set(qn("w:val"), "true")
    properties.append(repeat)


def add_table(document: Document, headers: list[str], rows: list[list[str]],
              widths_dxa: list[int]) -> None:
    table = document.add_table(rows=1, cols=len(headers))
    table.style = "Table Grid"
    header = table.rows[0]
    repeat_header(header)
    for index, value in enumerate(headers):
        cell = header.cells[index]
        cell.text = value
        set_cell_shading(cell, LIGHT_FILL)
        for paragraph in cell.paragraphs:
            paragraph.alignment = WD_ALIGN_PARAGRAPH.CENTER if index == 0 else WD_ALIGN_PARAGRAPH.LEFT
            paragraph.paragraph_format.space_after = Pt(0)
            for run in paragraph.runs:
                set_run_font(run, size=9.5, color=DARK, bold=True)

    for row_values in rows:
        cells = table.add_row().cells
        for index, value in enumerate(row_values):
            cells[index].text = str(value)
            for paragraph in cells[index].paragraphs:
                paragraph.paragraph_format.space_after = Pt(0)
                paragraph.paragraph_format.line_spacing = 1.15
                if index == 0:
                    paragraph.alignment = WD_ALIGN_PARAGRAPH.CENTER
                for run in paragraph.runs:
                    set_run_font(run, size=9.2, color=DARK)

    set_table_geometry(table, widths_dxa)
    document.add_paragraph().paragraph_format.space_after = Pt(0)


def add_bullet(document: Document, text: str, numbered: bool = False) -> None:
    paragraph = document.add_paragraph(style="List Number" if numbered else "List Bullet")
    paragraph.add_run(text)


def add_callout(document: Document, title: str, text: str) -> None:
    table = document.add_table(rows=1, cols=1)
    table.style = "Table Grid"
    repeat_header(table.rows[0])
    cell = table.cell(0, 0)
    set_cell_shading(cell, GREEN_FILL)
    heading = cell.paragraphs[0]
    heading.paragraph_format.space_after = Pt(3)
    title_run = heading.add_run(title)
    set_run_font(title_run, size=10.5, color=FOREST, bold=True)
    paragraph = cell.add_paragraph(text)
    paragraph.paragraph_format.space_after = Pt(0)
    set_table_geometry(table, [9360])
    document.add_paragraph().paragraph_format.space_after = Pt(0)


def find_font(size: int):
    candidates = [
        Path("C:/Windows/Fonts/arial.ttf"),
        Path("C:/Windows/Fonts/calibri.ttf"),
    ]
    for candidate in candidates:
        if candidate.exists():
            return ImageFont.truetype(str(candidate), size)
    return ImageFont.load_default()


def draw_centered_text(draw: ImageDraw.ImageDraw, box: tuple[int, int, int, int],
                       text: str, font, fill: str) -> None:
    left, top, right, bottom = box
    bounds = draw.multiline_textbbox((0, 0), text, font=font, spacing=5, align="center")
    width = bounds[2] - bounds[0]
    height = bounds[3] - bounds[1]
    draw.multiline_text(((left + right - width) / 2, (top + bottom - height) / 2), text,
                        font=font, fill=fill, spacing=5, align="center")


def build_diagrams() -> tuple[Path, Path]:
    ASSET_DIR.mkdir(parents=True, exist_ok=True)
    use_case_path = ASSET_DIR / "proceso_compra.png"
    er_path = ASSET_DIR / "modelo_relacional.png"

    image = Image.new("RGB", (1500, 900), "#fffdf8")
    draw = ImageDraw.Draw(image)
    title_font = find_font(36)
    box_font = find_font(24)
    draw.text((55, 35), "Casos de uso principales", font=title_font, fill="#1f6847")
    boxes = [
        (60, 130, 350, 260, "Visitante\nExplorar y filtrar"),
        (440, 130, 730, 260, "Registrarse e\niniciar sesión"),
        (820, 130, 1110, 260, "Cliente\nGestionar carrito"),
        (1150, 375, 1440, 505, "Comprar con tarjeta\no PayPal simulado"),
        (820, 620, 1110, 750, "Ver confirmación,\nseguimiento y factura"),
        (440, 620, 730, 750, "Perfil e historial\nde pedidos"),
        (60, 620, 350, 750, "Administrador\nPedidos y reportes"),
    ]
    for left, top, right, bottom, label in boxes:
        draw.rounded_rectangle((left, top, right, bottom), radius=22, fill="#e7f0e9", outline="#1f6847", width=4)
        draw_centered_text(draw, (left, top, right, bottom), label, box_font, "#17251d")
    arrows = [((350, 195), (440, 195)), ((730, 195), (820, 195)), ((1110, 195), (1295, 375)),
              ((1295, 505), (1110, 685)), ((820, 685), (730, 685)), ((440, 685), (350, 685))]
    for start, end in arrows:
        draw.line((start, end), fill="#c7663f", width=8)
        x, y = end
        draw.polygon([(x, y), (x - 18 if x > start[0] else x + 18, y - 13),
                      (x - 18 if x > start[0] else x + 18, y + 13)], fill="#c7663f")
    image.save(use_case_path, quality=95)

    image = Image.new("RGB", (1500, 760), "#fffdf8")
    draw = ImageDraw.Draw(image)
    draw.text((55, 30), "Modelo relacional simplificado", font=title_font, fill="#1f6847")
    entities = {
        "USERS": (70, 155, 340, 300),
        "ORDERS": (615, 155, 885, 300),
        "PAYMENTS": (1160, 155, 1430, 300),
        "CATEGORIES": (70, 500, 340, 645),
        "PRODUCTS": (615, 500, 885, 645),
        "ORDER_ITEMS": (1160, 500, 1430, 645),
    }
    for label, box in entities.items():
        draw.rounded_rectangle(box, radius=18, fill="#e8eef5", outline="#1f6847", width=4)
        draw_centered_text(draw, box, label, box_font, "#17251d")
    relations = [
        ((340, 225), (615, 225), "1 : N"), ((885, 225), (1160, 225), "1 : 1"),
        ((340, 572), (615, 572), "1 : N"), ((885, 572), (1160, 572), "1 : N"),
        ((750, 300), (1295, 500), "1 : N"),
    ]
    small_font = find_font(20)
    for start, end, label in relations:
        draw.line((start, end), fill="#c7663f", width=6)
        mid = ((start[0] + end[0]) // 2, (start[1] + end[1]) // 2)
        draw.rounded_rectangle((mid[0] - 38, mid[1] - 18, mid[0] + 38, mid[1] + 18), radius=8, fill="#fffdf8")
        draw.text((mid[0] - 25, mid[1] - 12), label, font=small_font, fill="#c7663f")
    image.save(er_path, quality=95)
    return use_case_path, er_path


def set_alt_text(picture_run, description: str) -> None:
    drawing = picture_run._r.find(qn("w:drawing"))
    if drawing is None:
        return
    document_properties = drawing.find(".//" + qn("wp:docPr"))
    if document_properties is not None:
        document_properties.set("descr", description)


def add_figure(document: Document, path: Path, caption: str, alt: str) -> None:
    paragraph = document.add_paragraph()
    paragraph.alignment = WD_ALIGN_PARAGRAPH.CENTER
    run = paragraph.add_run()
    run.add_picture(str(path), width=Inches(6.3))
    set_alt_text(run, alt)
    caption_paragraph = document.add_paragraph()
    caption_paragraph.alignment = WD_ALIGN_PARAGRAPH.CENTER
    caption_paragraph.paragraph_format.space_after = Pt(8)
    caption_run = caption_paragraph.add_run(caption)
    set_run_font(caption_run, size=9, color=MUTED, italic=True)


def add_cover(document: Document) -> None:
    document.add_paragraph().paragraph_format.space_after = Pt(72)
    kicker = document.add_paragraph()
    kicker.alignment = WD_ALIGN_PARAGRAPH.CENTER
    run = kicker.add_run("PROYECTO FINAL  |  ITI-523")
    set_run_font(run, size=10.5, color=RUST, bold=True)
    kicker.paragraph_format.space_after = Pt(20)

    title = document.add_paragraph()
    title.alignment = WD_ALIGN_PARAGRAPH.CENTER
    title.paragraph_format.space_after = Pt(8)
    run = title.add_run("CAFÉ SALAS")
    set_run_font(run, size=31, color=FOREST, bold=True)

    subtitle = document.add_paragraph()
    subtitle.alignment = WD_ALIGN_PARAGRAPH.CENTER
    subtitle.paragraph_format.space_after = Pt(80)
    run = subtitle.add_run("Tienda virtual de café y productos costarricenses")
    set_run_font(run, size=15, color=DARK)

    meta = document.add_table(rows=5, cols=2)
    meta.style = "Table Grid"
    repeat_header(meta.rows[0])
    for index, value in enumerate(("Campo", "Detalle")):
        meta.cell(0, index).text = value
        set_cell_shading(meta.cell(0, index), LIGHT_FILL)
        for run in meta.cell(0, index).paragraphs[0].runs:
            set_run_font(run, bold=True, color=DARK)
    data = [
        ("Curso", "Tecnologías y Sistemas Web II"),
        ("Docente", "Ing. Milena Vargas Blanco"),
        ("Participantes", "Byron Chacón y Franklin Castillo"),
        ("Fecha", "25 y 26 de agosto de 2026"),
    ]
    for index, (label, value) in enumerate(data, start=1):
        meta.cell(index, 0).text = label
        meta.cell(index, 1).text = value
        set_cell_shading(meta.cell(index, 0), GREEN_FILL)
        for run in meta.cell(index, 0).paragraphs[0].runs:
            set_run_font(run, bold=True, color=FOREST)
    set_table_geometry(meta, [2700, 6660])

    paragraph = document.add_paragraph()
    paragraph.alignment = WD_ALIGN_PARAGRAPH.CENTER
    paragraph.paragraph_format.space_before = Pt(55)
    run = paragraph.add_run("Universidad Técnica Nacional - Costa Rica")
    set_run_font(run, size=10.5, color=MUTED, italic=True)
    paragraph.add_run().add_break(WD_BREAK.PAGE)


def build_document() -> None:
    use_case_diagram, er_diagram = build_diagrams()
    document = Document()
    configure_page(document)
    configure_styles(document)
    configure_header_footer(document)
    add_cover(document)

    document.add_heading("Contenido", level=1)
    toc_items = [
        "1. Resumen y alcance", "2. Tecnologías", "3. Instalación principal con SQLite",
        "4. Arquitectura MVC", "5. Modelo de datos", "6. Proceso de compra",
        "7. Funcionalidades y reglas", "8. Seguridad", "9. Pruebas",
        "10. Matriz de cumplimiento", "11. Exposición y operación",
    ]
    for item in toc_items:
        add_bullet(document, item)

    document.add_heading("1. Resumen y alcance", level=1)
    document.add_paragraph(
        "Café Salas es una tienda virtual académica para café y productos artesanales costarricenses. "
        "Incluye autenticación, perfil, catálogo, búsqueda, carrito, compra, factura, seguimiento, cookies "
        "de productos recientes, administración y reportes PDF. La implementación se inspira directamente "
        "en los temas de Laravel explicados en las sesiones 9 y 10 del curso."
    )
    add_callout(document, "Resultado verificable", "La batería automatizada se ejecuta con SQLite aislado. El resultado exacto de la revisión final consta en docs/PRUEBAS.md, junto con formato, compilación y auditoría de dependencias.")

    document.add_heading("2. Tecnologías", level=1)
    add_table(document, ["Capa", "Tecnología y propósito"], [
        ["Backend", "PHP 8.2 y Laravel 12 bajo el patrón MVC."],
        ["Base principal", "SQLite mediante database/database.sqlite, como exige la consigna."],
        ["Base alternativa", "MariaDB/MySQL de XAMPP, administrable desde phpMyAdmin."],
        ["Frontend", "Blade, HTML5, Bootstrap 5.3, CSS responsive y JavaScript."],
        ["PDF", "Dompdf 3.1.6 para factura y ventas por mes o cliente."],
        ["Calidad", "PHPUnit, Laravel Pint y GitHub Actions."],
    ], [2200, 7160])

    document.add_heading("3. Instalación principal con SQLite", level=1)
    for step in [
        "Ejecutar composer install dentro de la carpeta del proyecto.",
        "Copiar .env.example a .env y ejecutar php artisan key:generate.",
        "Crear database/database.sqlite si todavía no existe.",
        "Verificar DB_CONNECTION=sqlite y DB_DATABASE=database/database.sqlite.",
        "Ejecutar php artisan migrate:fresh --seed.",
        "Ejecutar php artisan serve y abrir http://127.0.0.1:8000.",
    ]:
        add_bullet(document, step, numbered=True)
    document.add_heading("MariaDB/MySQL como alternativa", level=2)
    document.add_paragraph("Para una demostración opcional con XAMPP, copie .env.mysql.example a .env, cree cafe_salas en phpMyAdmin y ejecute las migraciones. También puede importar database/sql/cafe_salas.sql.")
    add_callout(document, "Apache de XAMPP", "Si se usa un VirtualHost, el DocumentRoot debe apuntar a la carpeta public. El ejemplo deployment/apache-vhost.conf.example evita exponer .env o vendor.")

    document.add_heading("4. Arquitectura MVC", level=1)
    for label, text in [
        ("Modelos", "User, Category, Product, Order, OrderItem y Payment representan tablas y relaciones Eloquent."),
        ("Vistas", "Blade presenta HTML escapado y reutiliza el componente product-card."),
        ("Controladores", "Validan solicitudes, coordinan modelos y retornan vistas, redirecciones o PDF."),
        ("Rutas", "Usan nombres, verbos HTTP y grupos guest, auth y admin."),
        ("Servicios", "Carrito, PDF, pagos simulados, productos recientes y estados de pedido."),
        ("Middleware", "AdminMiddleware autoriza el panel y SecurityHeaders agrega defensas HTTP."),
    ]:
        paragraph = document.add_paragraph()
        run = paragraph.add_run(label + ": ")
        set_run_font(run, bold=True, color=FOREST)
        paragraph.add_run(text)

    document.add_heading("5. Modelo de datos", level=1)
    add_figure(document, er_diagram, "Figura 1. Relaciones principales de la base de datos.", "Diagrama de relaciones entre usuarios, pedidos, pagos, categorías, productos y detalles.")
    add_table(document, ["Tabla", "Responsabilidad"], [
        ["users", "Identidad, perfil y rol administrativo."],
        ["categories / products", "Catálogo, precios, imágenes, estado e inventario."],
        ["orders", "Cabecera: cliente, fecha, dirección, estado y totales."],
        ["order_items", "Productos, cantidades y precios históricos de la compra."],
        ["payments", "Método, estado, referencia y últimos cuatro dígitos."],
        ["sessions", "Sesiones cifradas administradas por Laravel."],
    ], [2500, 6860])

    document.add_heading("6. Proceso de compra", level=1)
    add_figure(document, use_case_diagram, "Figura 2. Casos de uso principales.", "Acciones del visitante, cliente y administrador en Café Salas.")
    document.add_paragraph("Actores: visitante, cliente, administrador y pasarela simulada. El cliente puede completar el pedido solo después de autenticarse; el administrador no interviene en el pago, pero controla estados y reportes.")

    document.add_heading("7. Funcionalidades y reglas", level=1)
    for item in [
        "Registro, login, logout, perfil editable e historial paginado.",
        "Catálogo por categorías, detalle, imágenes, búsqueda, precios y ordenamiento.",
        "Carrito en sesión con agregar, actualizar y eliminar.",
        "IVA de 13 %, envío de CRC 2.500 y envío gratis desde CRC 20.000.",
        "Chocolate oscuro 82 % y caja de las ocho regiones cafetaleras de Costa Rica.",
        "Tarjeta y PayPal simulados; confirmación, factura y seguimiento único.",
        "Cookie cifrada con hasta seis productos vistos durante 30 días.",
        "Panel administrativo, inventario bajo y estados de pedido.",
        "Cancelación idempotente con reembolso simulado y devolución de inventario.",
        "Reportes PDF de ventas por mes y por cliente.",
    ]:
        add_bullet(document, item)

    document.add_heading("8. Seguridad", level=1)
    add_table(document, ["Riesgo", "Control aplicado"], [
        ["Inyección SQL", "Eloquent, consultas parametrizadas y validación de filtros."],
        ["XSS", "Escape Blade; prueba automatizada con una etiqueta script."],
        ["CSRF", "Token @csrf en formularios POST, PATCH y DELETE."],
        ["Robo de sesión", "Regeneración en login, invalidación en logout, cifrado, HttpOnly y SameSite."],
        ["Acceso indebido", "Middleware admin y comprobación de propietario en facturas."],
        ["Datos de tarjeta", "No se almacenan ni se conservan en sesión; solo referencia y cuatro dígitos."],
        ["Carrera de inventario", "Transacción de base y validación final antes del descuento."],
        ["Navegador", "CSP, anti-frame, nosniff, Referrer Policy y páginas privadas sin caché."],
        ["Transporte", "HTTPS forzado en producción, HSTS y cookie Secure en el perfil productivo."],
        ["Dependencias", "Dompdf 3.1.6 y composer audit sin avisos conocidos."],
    ], [2600, 6760])
    add_callout(document, "Pasarela académica", "No se realizan cargos reales. Una pasarela productiva exige credenciales del comercio, webhooks y cumplimiento del proveedor.")

    document.add_heading("9. Pruebas", level=1)
    add_table(document, ["Suite", "Cobertura"], [
        ["CartTotalsTest", "IVA, envío y envío gratis."],
        ["AuthenticationTest", "Registro, hash, login, logout y validaciones."],
        ["CatalogAndCookieTest", "Filtros, cookie, XSS y datos reales del catálogo."],
        ["CartTest", "Altas, cambios, bajas, total e inventario."],
        ["CheckoutTest", "Pago, seguimiento, inventario, privacidad y autorización."],
        ["EndToEndPurchaseTest", "Registro hasta factura e historial en una sesión."],
        ["OrderManagementTest", "Estados, cancelación, pago e inventario."],
        ["SecurityHardeningTest", "CSP, HSTS, permisos y caché privada."],
        ["ProfileAndReportsTest", "Perfil, historial, rol admin y ambos PDF."],
    ], [2700, 6660])
    document.add_paragraph("Comandos: php artisan test y php vendor/bin/pint --test. PHPUnit usa SQLite en memoria y no modifica cafe_salas.")

    document.add_heading("10. Matriz de cumplimiento", level=1)
    rubric = [
        (1, "Entrega a tiempo", "Paquete preparado; carga a cargo del equipo."),
        (2, "Carpeta comprimida identificada", "ProyectoFinal-ByronChacon-FranklinCastillo.zip."),
        (3, "Autenticación y usuarios", "AuthController, sesiones y middleware."),
        (4, "Registro", "Formulario, validación y prueba."),
        (5, "Login y logout", "Sesión segura y limitación de intentos."),
        (6, "Perfil e historial", "Edición y pedidos paginados."),
        (7, "Categorías", "Modelo y relación Eloquent."),
        (8, "Detalles e imágenes", "Ocho productos con fotografías locales."),
        (9, "Búsqueda y filtros", "Nombre, categoría, precio y orden."),
        (10, "Carrito", "Agregar, actualizar y eliminar."),
        (11, "Impuesto y envío", "IVA 13 % y reglas verificadas."),
        (12, "Compra o factura", "ID, fecha, detalle y montos."),
        (13, "Tarjeta y PayPal", "Opciones validadas y simuladas."),
        (14, "Confirmación y seguimiento", "Números únicos."),
        (15, "Reportes", "PDF mensual y por cliente."),
        (16, "PHP y SQLite", "Laravel/PHP con SQLite como base principal."),
        (17, "Frontend", "Bootstrap, CSS propio y fotografías reales."),
        (18, "Validación", "Servidor, CSRF y mensajes."),
        (19, "Cookie", "recent_products cifrada."),
        (20, "Mostrar recientes", "Sección visible en inicio."),
        (21, "Código completo", "Fuente, SQL, pruebas y docs."),
        (22, "Documentación", "README, MD y este DOCX."),
        (23, "Pruebas automatizadas", "PHPUnit con SQLite aislado y reporte reproducible."),
        (24, "Exposición", "Guion preparado; asistencia humana."),
        (25, "Funciones especificadas", "Trazadas en esta matriz."),
        (26, "Responsive y UX", "Validado en escritorio y móvil."),
        (27, "Seguridad", "Defensas documentadas y probadas."),
        (28, "Código y buenas prácticas", "MVC, servicios, Pint y tests."),
        (29, "Pregunta docente 1", "Banco de respuestas preparado."),
        (30, "Pregunta docente 2", "Banco de respuestas preparado."),
        (31, "Pregunta docente 3", "Banco de respuestas preparado."),
        (32, "GitHub", "Repositorio real documentado; no se inventa un enlace cafe-salas."),
    ]
    add_table(document, ["N.", "Criterio", "Evidencia"], [[str(n), item, evidence] for n, item, evidence in rubric], [650, 3300, 5410])

    document.add_heading("11. Exposición y operación", level=1)
    add_callout(
        document,
        "Prevención de sanciones",
        "No responder al menos el 50 % de las preguntas puede anular hasta el 75 % del valor del proyecto. "
        "Cada integrante debe comprender y demostrar su módulo. La asistencia de Codex se declara y se acompaña "
        "con adaptación, pruebas, historial Git y documentación; ocultarla no demuestra autoría.",
    )
    document.add_heading("Recorrido sugerido", level=2)
    for item in [
        "Presentar MVC, migraciones y tablas SQLite.",
        "Filtrar productos y mostrar cookie de recientes.",
        "Modificar carrito y explicar cálculos.",
        "Completar compra simulada y descargar factura.",
        "Mostrar historial, panel y reportes PDF.",
        "Ejecutar pruebas y mostrar GitHub Actions.",
    ]:
        add_bullet(document, item, numbered=True)

    document.add_heading("Credenciales de demostración", level=2)
    add_table(document, ["Rol", "Correo", "Clave"], [
        ["Administrador", "admin@cafesalas.test", "Admin123!"],
        ["Cliente", "cliente@cafesalas.test", "Cliente123!"],
    ], [2100, 4500, 2760])

    document.add_heading("Límites externos", level=2)
    document.add_paragraph("La asistencia y las respuestas de la exposición son responsabilidad de Byron Chacón y Franklin Castillo. El repositorio privado real es github.com/Byroncha1323/cafe-salas. El hosting y un certificado público requieren cuenta, dominio y autorización del equipo.")
    add_callout(document, "Uso responsable de herramientas", "Para apoyar la revisión se utilizó OpenAI Codex. Byron Chacón y Franklin Castillo deben revisar, comprender, adaptar y poder explicar el código, las pruebas y la documentación antes de entregar.")

    document.core_properties.title = "Café Salas — Tienda virtual de café y productos costarricenses"
    document.core_properties.subject = "Proyecto final de Tecnologías y Sistemas Web II, ITI-523"
    document.core_properties.author = "Byron Chacón y Franklin Castillo"
    document.core_properties.keywords = "Laravel, PHP, SQLite, Café Salas, tienda virtual, ITI-523"
    OUTPUT.parent.mkdir(parents=True, exist_ok=True)
    document.save(OUTPUT)


if __name__ == "__main__":
    build_document()
