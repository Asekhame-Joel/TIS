(function () {
  "use strict";

  var card = document.getElementById("payment-status-card");
  var symbol = document.getElementById("status-symbol");
  var title = document.getElementById("status-title");
  var message = document.getElementById("status-message");
  var progressLabel = document.getElementById("status-progress-label");
  var progressHint = document.getElementById("status-progress-hint");
  var assuranceText = document.getElementById("status-assurance-text");
  var details = document.getElementById("status-details");
  var ticket = document.getElementById("status-ticket");
  var tier = document.getElementById("status-tier");
  var amount = document.getElementById("status-amount");
  var referenceField = document.getElementById("status-reference");
  var viewTicket = document.getElementById("view-ticket");
  var download = document.getElementById("download-ticket");
  var checkAgain = document.getElementById("check-again");
  var params = new URLSearchParams(window.location.search);
  var reference = params.get("reference") || params.get("trxref");
  var attempts = 0;
  var timer;
  var firstCheckStartedAt = Date.now();
  var minimumLoaderTime = 1200;

  function setState(state) {
    card.classList.remove("is-loading", "is-pending", "is-success", "is-error");
    card.classList.add("is-" + state);
  }

  function waitForInitialLoader() {
    var remaining = minimumLoaderTime - (Date.now() - firstCheckStartedAt);
    return remaining > 0
      ? new Promise(function (resolve) { window.setTimeout(resolve, remaining); })
      : Promise.resolve();
  }

  function showPending(text) {
    setState("pending");
    symbol.textContent = "...";
    title.textContent = "Payment is still processing";
    message.textContent = text || "We have not received final confirmation yet. Bank transfers can take a few moments.";
    progressLabel.textContent = "Waiting for bank confirmation";
    progressHint.textContent = "We will check again automatically";
    assuranceText.textContent = "Keep this page open. Your ticket will appear as soon as Paystack confirms the payment.";
    checkAgain.classList.add("is-visible");
    card.setAttribute("aria-busy", "true");
  }

  function showError(text) {
    window.clearTimeout(timer);
    setState("error");
    symbol.textContent = "!";
    title.textContent = "We could not confirm this payment";
    message.textContent = text || "Please check the payment reference or contact the ticket team for help.";
    progressLabel.textContent = "Verification interrupted";
    progressHint.textContent = "Your payment has not been marked as complete";
    assuranceText.textContent = "No ticket was issued. You can safely check again or contact the ticket team.";
    checkAgain.classList.add("is-visible");
    card.setAttribute("aria-busy", "false");
  }

  function showSuccess(data) {
    window.clearTimeout(timer);
    setState("success");
    symbol.textContent = "\u2713";
    title.textContent = "Your " + data.tier + " ticket is ready";
    message.textContent = data.email_sent
      ? "Payment confirmed. We have emailed your ticket, and you can also download it below."
      : "Payment confirmed. Your ticket is ready to download; the email is being retried automatically.";
    progressLabel.textContent = "Payment verified";
    progressHint.textContent = "Your ticket is secured";
    assuranceText.textContent = data.email_sent
      ? "A copy has been sent to the email address used during checkout."
      : "Your download is ready while we continue retrying email delivery.";
    ticket.textContent = data.ticket_number;
    tier.textContent = data.tier;
    amount.textContent = data.amount_display;
    referenceField.textContent = data.reference;
    details.classList.add("is-visible");
    viewTicket.href = data.view_url || data.download_url;
    viewTicket.classList.add("is-visible");
    download.href = data.download_url;
    download.setAttribute("download", data.ticket_number + ".pdf");
    download.classList.add("is-visible");
    checkAgain.classList.remove("is-visible");
    card.setAttribute("aria-busy", "false");
  }

  async function verifyPayment() {
    if (!reference) {
      showError("No payment reference was supplied. If you completed a payment, check your email or contact the ticket team.");
      return;
    }

    attempts += 1;
    checkAgain.disabled = true;
    if (attempts > 1) {
      progressLabel.textContent = "Checking Paystack again";
      progressHint.textContent = "Attempt " + attempts;
    }

    try {
      var response = await fetch("/api/payment/verify.php?reference=" + encodeURIComponent(reference), {
        headers: { "Accept": "application/json" },
        cache: "no-store"
      });
      var result = await response.json();

      if (response.ok && result.success && result.status === "success") {
        if (attempts === 1) await waitForInitialLoader();
        showSuccess(result.ticket);
        return;
      }

      if (response.status === 202 || result.status === "pending") {
        showPending(result.message);
        if (attempts < 12) timer = window.setTimeout(verifyPayment, 5000);
        return;
      }

      throw new Error(result.message || "Payment verification failed.");
    } catch (error) {
      showError(error.message);
    } finally {
      checkAgain.disabled = false;
    }
  }

  checkAgain.addEventListener("click", function () {
    setState("loading");
    card.setAttribute("aria-busy", "true");
    title.textContent = "Checking again";
    message.textContent = "Please wait while we ask Paystack for the latest transaction status.";
    progressLabel.textContent = "Refreshing payment status";
    progressHint.textContent = "Secure verification in progress";
    assuranceText.textContent = "Keep this page open while we check your transaction.";
    verifyPayment();
  });

  verifyPayment();
})();
