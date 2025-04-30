document.addEventListener('DOMContentLoaded', () => {
    loadTableData('Reviews');
});

function moderateReview(reviewId, status) {
    fetch(`./api/index.php?action=moderate_review&review_id=${reviewId}&status=${status}`, {
        method: 'POST',
        credentials: 'include'
    })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                loadTableData('Reviews');
            } else {
                alert(result.error);
            }
        })
        .catch(error => alert('Ошибка модерации: ' + error));
}