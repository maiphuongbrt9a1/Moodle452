function toggleAllCheckboxes(params) {
  const checkboxes = document.getElementsByClassName("select-checkbox");
  Array.from(checkboxes).forEach((checkbox) => {
    checkbox.checked = params.checked;
  });
}

/**
 * Lấy tất cả các ID của các checkbox 'select-checkbox' đã được chọn.
 * @returns {Array<string>} Mảng chứa các giá trị (IDs) của các checkbox đã chọn.
 */
function getSelectedCourseIds() {
  const selectedIds = [];
  const checkboxes = document.getElementsByClassName("select-checkbox"); // Lấy tất cả checkbox khóa học
  Array.from(checkboxes).forEach((checkbox) => {
    if (checkbox.checked) {
      selectedIds.push(checkbox.value); // Lấy giá trị (course ID)
    }
  });
  return selectedIds;
}

document.addEventListener("DOMContentLoaded", function () {
  // Lấy form bằng class hoặc id của nó
  var form = document.querySelector(".mform"); // Moodle form có class là 'mform'

  if (form) {
    form.addEventListener("submit", function () {
      var loadingOverlay = document.getElementById("loading-overlay");
      if (loadingOverlay) {
        loadingOverlay.style.display = "flex"; // Hiển thị lớp phủ
      }
    });
  }
});
