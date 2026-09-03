/* Minimal vanilla JS: mobile nav, mobile dropdown, sticky header state, scroll reveal */
(function () {
  "use strict";

  var body = document.body;
  var header = document.querySelector(".site-header");
  var toggle = document.querySelector(".nav-toggle");

  if (toggle) {
    toggle.addEventListener("click", function () {
      var open = body.classList.toggle("nav-open");
      toggle.setAttribute("aria-expanded", open ? "true" : "false");
    });
  }

  // Dropdown tap behaviour on small screens
  document.querySelectorAll(".dropdown-toggle").forEach(function (btn) {
    btn.addEventListener("click", function (e) {
      if (window.matchMedia("(max-width: 1080px)").matches) {
        e.preventDefault();
        btn.parentElement.classList.toggle("is-open");
      }
    });
  });

  // Close mobile nav when a link is chosen
  document.querySelectorAll(".nav a:not(.dropdown-toggle)").forEach(function (a) {
    a.addEventListener("click", function () {
      body.classList.remove("nav-open");
      if (toggle) toggle.setAttribute("aria-expanded", "false");
    });
  });

  // Sticky header shadow
  function onScroll() {
    if (!header) return;
    header.classList.toggle("is-scrolled", window.scrollY > 8);
  }
  onScroll();
  window.addEventListener("scroll", onScroll, { passive: true });

  // Scroll reveal
  var items = document.querySelectorAll(".reveal");
  if (!("IntersectionObserver" in window)) {
    items.forEach(function (el) { el.classList.add("is-visible"); });
    return;
  }
  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add("is-visible");
        io.unobserve(entry.target);
      }
    });
  }, { rootMargin: "0px 0px -8% 0px", threshold: 0.12 });
  items.forEach(function (el) { io.observe(el); });
})();


const enquiryForm = document.getElementById("enquiry-form");

if (enquiryForm) {
  const statusMessage = document.getElementById("form-status");
  const submitButton = enquiryForm.querySelector('button[type="submit"]');

  enquiryForm.addEventListener("submit", async function (event) {
    event.preventDefault();

    submitButton.disabled = true;
    submitButton.innerHTML = "Sending...";
    statusMessage.style.display = "none";

    try {
      const response = await fetch(enquiryForm.action, {
        method: "POST",
        body: new FormData(enquiryForm)
      });

      const result = await response.json();

      if (!response.ok || !result.success) {
        throw new Error(result.message || "Submission failed");
      }

      enquiryForm.reset();

      statusMessage.textContent =
        "Thank you! Your enquiry has been submitted successfully.";
      statusMessage.style.display = "block";
      statusMessage.style.color = "#16794b";

      submitButton.textContent = "Sent ✓";

      setTimeout(function () {
        window.location.reload();
      }, 3000);
    } catch (error) {
      statusMessage.textContent =
        "Sorry, your enquiry could not be sent. Please try again.";
      statusMessage.style.display = "block";
      statusMessage.style.color = "#b42318";

      submitButton.disabled = false;
      submitButton.innerHTML =
        'Send enquiry <span class="arrow" aria-hidden="true">&rarr;</span>';
    }
  });
}