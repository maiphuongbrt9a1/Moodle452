function toggleAllCheckboxes(params) {
  const checkboxes = document.getElementsByClassName("select-checkbox");
  Array.from(checkboxes).forEach((checkbox) => {
    checkbox.checked = params.checked;
  });
}
