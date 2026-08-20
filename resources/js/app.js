import "./bootstrap";
import "/node_modules/preline/dist/preline";
import "./preline-livewire-init";
import "@phosphor-icons/web/regular";
import "@phosphor-icons/web/thin";
import "@phosphor-icons/web/light";
import "@phosphor-icons/web/bold";
import "@phosphor-icons/web/fill";
import "@phosphor-icons/web/duotone";

const phosphorWeights = { thin: "ph-thin", light: "ph-light", regular: "ph", bold: "ph-bold", fill: "ph-fill", duotone: "ph-duotone" };

function upgradePhosphorTags(root = document) {
  const elements = [...(root.querySelectorAll?.("*") ?? [])].filter((element) => element.tagName.toLowerCase().startsWith("ph-"));
  elements.forEach((element) => {
    const icon = document.createElement("i");
    icon.className = `${phosphorWeights[element.getAttribute("weight")] ?? "ph"} ${element.tagName.toLowerCase()}`;
    for (const attribute of element.attributes) {
      if (attribute.name !== "weight" && attribute.name !== "class") icon.setAttribute(attribute.name, attribute.value);
    }
    if (element.className) icon.classList.add(...element.className.split(/\s+/).filter(Boolean));
    element.replaceWith(icon);
  });
}

document.addEventListener("DOMContentLoaded", () => upgradePhosphorTags());
document.addEventListener("livewire:navigated", () => upgradePhosphorTags());
document.addEventListener("livewire:updated", (event) => upgradePhosphorTags(event.target ?? document));
