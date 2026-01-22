function toggle(source) {
    const checkboxes = document.querySelectorAll('input[name="id_log[]"]');
    checkboxes.forEach(cb => cb.checked = source.checked);
}
