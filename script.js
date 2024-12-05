document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("modal");
  const closeModal = document.getElementById("close-modal");
  const calendarDays = document.querySelectorAll(".calendar-day");
  const selectedDate = document.getElementById("selected-date");
  const saveBtn = document.getElementById("save-btn");

  let currentSelectedDate = "";

  // Open modal on date click
  calendarDays.forEach((day) => {
    day.addEventListener("click", () => {
      currentSelectedDate = day.getAttribute("data-date");
      selectedDate.textContent = currentSelectedDate;
      modal.style.display = "block";
    });
  });

  // Close modal
  closeModal.addEventListener("click", () => {
    modal.style.display = "none";
  });

  // Save event
  // Save event
  saveBtn.addEventListener("click", () => {
    const content = CKEDITOR.instances.editor.getData(); // Fetch CKEditor content
    if (!content) return alert("Content cannot be empty.");

    fetch("save_data.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ date: currentSelectedDate, content }),
    })
      .then((response) => response.text())
      .then((data) => {
        alert(data);
        modal.style.display = "none";
        window.location.reload();
      })
      .catch((error) => console.error("Error:", error));
  });
});
