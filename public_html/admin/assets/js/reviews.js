// Reviews page JS – defines modal helpers and actions in global scope
;(function () {
  function qs(sel, root) {
    return (root || document).querySelector(sel)
  }

  window.openReviewModal = function () {
    const modal = qs('#reviewModal')
    if (!modal) return
    const title = qs('#modalTitle')
    const form = qs('#reviewForm')
    const id = qs('#reviewId')
    if (title) title.textContent = 'Добавить отзыв'
    if (form) form.reset()
    if (id) id.value = ''
    modal.style.display = 'block'
  }

  window.closeReviewModal = function () {
    const modal = qs('#reviewModal')
    if (modal) modal.style.display = 'none'
  }

  window.editReview = function (
    reviewId,
    name,
    email,
    rating,
    content,
    status
  ) {
    const modal = qs('#reviewModal')
    if (!modal) return
    qs('#modalTitle').textContent = 'Редактировать отзыв'
    qs('#reviewId').value = reviewId || ''
    qs('#reviewName').value = name || ''
    qs('#reviewEmail').value = email || ''
    qs('#reviewRating').value = String(rating || '')
    qs('#reviewContent').value = content || ''
    qs('#reviewStatus').value = status || 'approved'
    modal.style.display = 'block'
  }

  window.resetReview = function (reviewId) {
    if (!reviewId) return
    if (!confirm('Сбросить статус отзыва в "Ожидает модерации"?')) return
    const form = document.createElement('form')
    form.method = 'POST'
    form.innerHTML =
      '<input type="hidden" name="csrf_token" value="' +
      (window.adminCSRFToken || '') +
      '">' +
      '<input type="hidden" name="action" value="update_status">' +
      '<input type="hidden" name="review_id" value="' +
      reviewId +
      '">' +
      '<input type="hidden" name="status" value="pending">'
    document.body.appendChild(form)
    form.submit()
  }

  window.deleteReview = function (reviewId) {
    if (!reviewId) return
    if (!confirm('Удалить отзыв? Это действие нельзя отменить.')) return
    const form = document.createElement('form')
    form.method = 'POST'
    form.innerHTML =
      '<input type="hidden" name="csrf_token" value="' +
      (window.adminCSRFToken || '') +
      '">' +
      '<input type="hidden" name="action" value="delete">' +
      '<input type="hidden" name="review_id" value="' +
      reviewId +
      '">'
    document.body.appendChild(form)
    form.submit()
  }

  // Delegation fallback
  document.addEventListener('click', function (e) {
    const el = e.target.closest('[data-action]')
    if (!el) return
    const id = el.dataset.id
    switch (el.dataset.action) {
      case 'open-review-modal':
        window.openReviewModal()
        break
      case 'close-review-modal':
        window.closeReviewModal()
        break
      case 'edit-review':
        window.editReview(
          id,
          el.dataset.name || '',
          el.dataset.email || '',
          el.dataset.rating || '',
          el.dataset.content || '',
          el.dataset.status || 'approved'
        )
        break
      case 'reset-review':
        window.resetReview(id)
        break
      case 'delete-review':
        window.deleteReview(id)
        break
      case 'export-reviews':
        if (typeof exportReviews === 'function') exportReviews()
        break
    }
  })
})()
