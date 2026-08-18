import "preline";

// Initialize Preline UI components
function initPrelineComponents() {
  // Use the recommended HSStaticMethods.autoInit() approach
  if (
    window.HSStaticMethods &&
    typeof window.HSStaticMethods.autoInit === "function"
  ) {
    window.HSStaticMethods.autoInit();
  }
}

// Listen for Livewire events to re-initialize components
document.addEventListener("livewire:navigated", () => {
  // Re-initialize components after navigation
  initPrelineComponents();
});

document.addEventListener("livewire:updated", () => {
  initPrelineComponents();
});

document.addEventListener("livewire:load", () => {
  initPrelineComponents();
});

// Initialize on page load
document.addEventListener("livewire:init", () => {
  initPrelineComponents();
});
