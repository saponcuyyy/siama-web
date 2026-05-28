import DOMPurify from 'dompurify';

export function sanitize(html) {
    if (!html) return '';
    return DOMPurify.sanitize(html, {
        ALLOWED_TAGS: ['p', 'br', 'b', 'i', 'u', 'strong', 'em', 'ol', 'ul', 'li', 'span', 'div', 'sub', 'sup', 'table', 'thead', 'tbody', 'tr', 'td', 'th', 'img', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'pre', 'code', 'blockquote', 'figure', 'figcaption'],
        ALLOWED_ATTR: ['src', 'alt', 'href', 'target', 'rel', 'class', 'style', 'width', 'height', 'align', 'colspan', 'rowspan'],
        ALLOW_DATA_ATTR: false,
    });
}
