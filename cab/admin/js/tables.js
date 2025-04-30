document.addEventListener('DOMContentLoaded', () => {
    const tableName = new URLSearchParams(window.location.search).get('table') || 'Products';
    loadTableData(tableName);
});