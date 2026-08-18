# Front-End UI Guidelines

Welcome to the comprehensive front-end guidelines for the Atlas project. This document outlines the standards, tools, and best practices we use to ensure a consistent, maintainable, and high-quality user interface across the application.

## 1. Core Technologies & UI Ecosystem

We rely on a curated set of tools and libraries to build our front-end:

- **Preline UI**: Serves as our overarching design system and theme. It dictates the overall aesthetic of the app. [Preline UI](https://preline.co/)
- **Sheaf UI**: Our primary source for pre-built, reusable Blade components. [Sheaf UI](https://sheafui.dev)
- **Livewire (v4)**: Used for all interactive pages by default, seamlessly blending back-end PHP logic with front-end interactivity. [Livewire](https://livewire.laravel.com/)
- **Filament Tables**: The designated package for handling all data tables, list views, and tabular needs. [Filament Tables](https://filamentphp.com/docs/tables)
- **Phosphor Icons**: Our default, unified icon pack. Avoid introducing other icon libraries to maintain visual consistency. [Phosphor Icons](https://phosphoricons.com/)
- **Tailwind CSS**: The underlying utility-first CSS framework driving our custom designs and component styles. [Tailwind CSS](https://tailwindcss.com/)

## 2. Component-Based Architecture

We strictly adhere to a component-based approach when building UIs.

- **Reusability First**: If a UI element appears in more than one place or represents a distinct logical block, it **must** be extracted into a reusable Blade component.
- **Component Discovery**: Before copying raw HTML/Tailwind markup from the Preline UI documentation or any other source, you **must check the Sheaf UI documentation**. If Sheaf UI has an equivalent component, install it via the CLI and use the Sheaf Blade component instead of using raw markup.
- **Custom Components**: For unique UI elements not covered by Preline or Sheaf, you may use any other source of visually compatible Tailwind markup and create standard Laravel Blade components.

## 3. Livewire Standards

All pages must be built using Livewire v4.

### Namespaces & Structure

We organize Livewire components into feature-based namespaces. Currently, there are 3 primary namespaces:

- `supplier` for supplier-only pages
- `staff` for staff-only pages
- `pages` for pages accessible to all

**Creating Livewire Components:**
When generating a new Livewire page or component, use the relevant namespace syntax to ensure files are placed in the correct directories.
For example:

```bash
php artisan make:livewire staff::applications.exemptions.create
```

This generates the component and places the PHP class and Blade view inside `resources/views/pages/students/applications/⚡exemptions`.

**Extensibility:**
You have the freedom to register additional namespaces in the Livewire configuration file (`config/livewire.php`) as the project grows and new distinct feature domains arise.

## 4. Theming (Dark Mode)

- **Mandatory Dual-Mode Testing**: The application supports both light and dark modes. It is **mandatory** to test every new feature, page, and component for compliance in both modes.
- **Implementation**: Rely on Tailwind's `dark:` variant classes to handle dark mode styling. When using Preline or Sheaf UI, ensure their native dark mode variants are visually robust and visually consistent with the rest of the application.

## 5. Tables and Data Presentation

- **Exclusivity**: Use **Filament Tables** for all tabular data presentation. Avoid building custom HTML tables manually.
- Leverage Filament's built-in column formatting, filtering, pagination, and bulk actions to keep data views consistent and feature-rich across the entire app.

## 6. General Best Practices

- **Separation of Concerns**: Keep Blade templates declarative and clean. Complex state manipulation and business logic belong in the Livewire component class.
- **Consistency**: Follow existing patterns in the codebase.
- **Responsiveness**: Ensure all views are mobile-first and responsive. Verify designs across mobile, tablet, and desktop breakpoints.
- **Accessibility (A11y)**: Write semantic HTML. Ensure forms have correctly labeled inputs, and interactive elements are keyboard accessible.

---

_These guidelines are designed to help us build a consistent and high-quality user interface for the CBU populace. Have fun!_
