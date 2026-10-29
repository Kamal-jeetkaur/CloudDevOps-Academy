// Client-side validation for the registration and feedback forms
document.addEventListener('DOMContentLoaded', function () {
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  const phonePattern = /^[0-9+\-\s]{7,20}$/;

  function showError(input, message) {
    input.classList.add('invalid');
    const err = input.closest('.form-group').querySelector('.field-error');
    if (err) err.textContent = message;
  }

  function clearError(input) {
    input.classList.remove('invalid');
    const err = input.closest('.form-group').querySelector('.field-error');
    if (err) err.textContent = '';
  }

  function attachLiveClear(form) {
    form.querySelectorAll('input, select, textarea').forEach(function (el) {
      el.addEventListener('input', function () { clearError(el); });
      el.addEventListener('change', function () { clearError(el); });
    });
  }

  // ---------- Registration form ----------
  const regForm = document.getElementById('registerForm');
  if (regForm) {
    attachLiveClear(regForm);
    regForm.addEventListener('submit', function (e) {
      let valid = true;
      const name = regForm.elements['full_name'];
      const email = regForm.elements['email'];
      const phone = regForm.elements['phone'];
      const track = regForm.elements['track'];
      const expChecked = regForm.querySelector('input[name="experience"]:checked');
      const terms = regForm.elements['terms'];

      if (name.value.trim().length < 2) { showError(name, 'Please enter your full name (min 2 characters).'); valid = false; }
      if (!emailPattern.test(email.value.trim())) { showError(email, 'Please enter a valid email address.'); valid = false; }
      if (!phonePattern.test(phone.value.trim())) { showError(phone, 'Enter a valid phone number (7-20 digits, may include + - space).'); valid = false; }
      if (track.value === '') { showError(track, 'Please select a training track.'); valid = false; }
      if (!expChecked) {
        const group = regForm.querySelector('.radio-row').closest('.form-group');
        group.querySelector('.field-error').textContent = 'Please select your experience level.';
        valid = false;
      }
      if (!terms.checked) { showError(terms, 'You must agree to continue.'); valid = false; }

      if (!valid) e.preventDefault();
    });

    regForm.querySelectorAll('input[name="experience"]').forEach(function (r) {
      r.addEventListener('change', function () {
        regForm.querySelector('.radio-row').closest('.form-group').querySelector('.field-error').textContent = '';
      });
    });
  }

  // ---------- Feedback form ----------
  const fbForm = document.getElementById('feedbackForm');
  if (fbForm) {
    attachLiveClear(fbForm);
    fbForm.addEventListener('submit', function (e) {
      let valid = true;
      const name = fbForm.elements['student_name'];
      const email = fbForm.elements['email'];
      const rating = fbForm.elements['rating'];
      const comments = fbForm.elements['comments'];

      if (name.value.trim().length < 2) { showError(name, 'Please enter your name.'); valid = false; }
      if (!emailPattern.test(email.value.trim())) { showError(email, 'Please enter a valid email address.'); valid = false; }
      if (rating.value === '') { showError(rating, 'Please choose a rating.'); valid = false; }
      if (comments.value.trim().length < 10) { showError(comments, 'Comments must be at least 10 characters.'); valid = false; }

      if (!valid) e.preventDefault();
    });

    const counter = document.getElementById('charCount');
    const ta = fbForm.elements['comments'];
    if (counter && ta) {
      ta.addEventListener('input', function () {
        counter.textContent = ta.value.length + ' / 1000 characters';
      });
    }
  }
});