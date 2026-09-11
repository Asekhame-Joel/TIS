(function () {
  "use strict";

  var dialog = document.getElementById("ticket-checkout");
  var form = document.getElementById("ticket-checkout-form");
  var status = document.getElementById("checkout-status");
  var openButtons = document.querySelectorAll("[data-open-checkout]");
  var closeButton = document.querySelector("[data-close-checkout]");
  var tierDataElement = document.getElementById("ticket-tier-data");
  var title = document.getElementById("checkout-title");
  var tierField = document.getElementById("checkout-tier");
  var summaryTier = document.getElementById("summary-tier");
  var summaryPrice = document.getElementById("summary-price");
  var summaryTotal = document.getElementById("summary-total");
  var summaryNote = document.getElementById("summary-note");
  var tiers = {};

  if (!dialog || !form || !status || !tierDataElement || !title || !tierField || !summaryTier || !summaryPrice || !summaryTotal) return;

  try {
    tiers = JSON.parse(tierDataElement.textContent || "{}");
  } catch (error) {
    return;
  }

  function selectTier(slug) {
    var selected = tiers[slug];
    if (!selected) return false;
    tierField.value = slug;
    title.textContent = selected.label;
    summaryTier.textContent = selected.label;
    summaryPrice.textContent = selected.ticket_price;
    summaryTotal.textContent = selected.checkout_total;
    if (summaryNote) {
      summaryNote.textContent = "Use a valid email address to receive your personalised " + selected.label + " ticket after successful payment.";
    }
    status.classList.remove("is-error");
    status.textContent = "";
    return true;
  }

  function openCheckout(slug) {
    if (!selectTier(slug || "premium")) return;
    if (typeof dialog.showModal === "function") {
      dialog.showModal();
    } else {
      dialog.setAttribute("open", "");
    }
    document.body.classList.add("checkout-open");
    window.setTimeout(function () {
      var firstField = document.getElementById("checkout-name");
      if (firstField) firstField.focus();
    }, 50);
  }

  function closeCheckout() {
    if (typeof dialog.close === "function") {
      dialog.close();
    } else {
      dialog.removeAttribute("open");
    }
    document.body.classList.remove("checkout-open");
  }

  openButtons.forEach(function (button) {
    button.addEventListener("click", function () {
      openCheckout(button.getAttribute("data-tier") || "premium");
    });
  });

  if (closeButton) closeButton.addEventListener("click", closeCheckout);

  dialog.addEventListener("click", function (event) {
    if (event.target === dialog) closeCheckout();
  });

  dialog.addEventListener("close", function () {
    document.body.classList.remove("checkout-open");
  });

  var requestedTier = new URLSearchParams(window.location.search).get("tier");
  if (requestedTier && tiers[requestedTier]) {
    openCheckout(requestedTier);
  }

  form.addEventListener("submit", async function (event) {
    event.preventDefault();

    if (!form.reportValidity()) return;

    var submitter = event.submitter;
    var paymentMethod = submitter && submitter.value ? submitter.value : "online";
    var buttons = form.querySelectorAll("button[type='submit']");
    var data = Object.fromEntries(new FormData(form).entries());
    data.payment_method = paymentMethod;

    buttons.forEach(function (button) { button.disabled = true; });
    status.classList.remove("is-error");
    status.textContent = paymentMethod === "bank_transfer"
      ? "Preparing your temporary bank account..."
      : "Opening secure Paystack checkout...";

    try {
      var response = await fetch("/api/payment/initialize.php", {
        method: "POST",
        headers: { "Content-Type": "application/json", "Accept": "application/json" },
        body: JSON.stringify(data)
      });
      var result = await response.json();

      if (!response.ok || !result.success || !result.authorization_url) {
        throw new Error(result.message || "Checkout could not be started.");
      }

      window.location.assign(result.authorization_url);
    } catch (error) {
      status.classList.add("is-error");
      status.textContent = error.message || "Checkout could not be started. Please try again.";
      buttons.forEach(function (button) { button.disabled = false; });
    }
  });
})();
