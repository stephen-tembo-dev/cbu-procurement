# Manual Test Guide — Purchase Manager

Complete end-to-end walkthrough for a pre-release smoke test.
Run this after a fresh `php artisan migrate:fresh --seed`.

---

## Test Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@binarypivot.test | password |
| Requester | requester@binarypivot.test | password |
| HOD (IT dept) | hod@binarypivot.test | password |
| Stores | stores@binarypivot.test | password |
| Bursar | bursar@binarypivot.test | password |
| VC | vc@binarypivot.test | password |
| Procurement | procurement@binarypivot.test | password |
| Auditor | auditor@binarypivot.test | password |

---

## Section 0 — Admin Setup (do this first)

### 0.1 Login as Admin

1. Go to `/login`, log in as `admin@binarypivot.test`.
2. Confirm the sidebar shows all sections: Dashboard, My Requisitions, New Requisition, Approvals, Management, Admin.

### 0.2 Verify Admin Pages Load

Navigate to each Admin sidebar link and confirm the page loads without errors:

| Sidebar link | Expected URL |
|---|---|
| Users | `/admin/users` |
| Departments | `/admin/departments` |
| Budget | `/admin/budget` |
| HOD Delegations | `/admin/hod-delegations` |

Navigate to each Management sidebar link:

| Sidebar link | Expected URL |
|---|---|
| Stock Items | `/admin/stock-items` |
| Suppliers | `/admin/suppliers` |

### 0.3 Seed Reference Data via Admin UI

**Add at least 2 Suppliers** (Management > Suppliers):
- `TechZambia Ltd` — any contact details
- `Office Depot Lusaka` — any contact details

**Add at least 2 Stock Items** (Management > Stock Items):
- `Printer Paper A4` — category: Stationery, unit: ream
- `USB Drive 32GB` — category: IT, unit: pcs

**Add a Budget Allocation** (Admin > Budget):
- Department: IT, Cost Centre: (any), amount: ZMW 50,000

> If cost centres don't exist, add them in Departments first.

### 0.4 Verify Departments Page

- Confirm IT department is listed.
- Confirm the HOD column shows `Bob HOD`.

---

## Section 1 — Sidebar Visibility per Role

Log in as each user below and confirm only the expected sidebar items are visible.

| Logged in as | Expected sidebar items |
|---|---|
| Requester | Dashboard, My Requisitions, New Requisition |
| HOD | Dashboard, My Requisitions, New Requisition, **Approvals** (All Requisitions, Reports) |
| Stores | Dashboard, **Approvals** (All Requisitions, Purchase Orders), **Management** (Stock Items) |
| Bursar | Dashboard, **Approvals** (All Requisitions, Purchase Orders, Reports) |
| VC | Dashboard, **Approvals** (All Requisitions, Purchase Orders, Reports) |
| Procurement | Dashboard, **Approvals** (All Requisitions, Purchase Orders, Reports), **Management** (Suppliers) |
| Auditor | Dashboard, **Approvals** (All Requisitions, Purchase Orders, Reports) |
| Admin | Dashboard, My Requisitions, New Requisition, Approvals (all), Management (Stock Items, Suppliers), Admin (Users, Departments, Budget, HOD Delegations) |

---

## Section 2 — Create a Purchase Requisition

**Login:** `requester@binarypivot.test`

1. Click **New Requisition** in the sidebar.
2. URL should be `/requisitions/create`.
3. Select Type: **Product / Goods**.
4. Select a Cost Centre from the dropdown (must be IT department's cost centre).
5. Fill in Notes: `Quarterly IT supplies requisition`.
6. Add Line Item 1:
   - Description: `Printer Paper A4`
   - Category: `Stationery`
   - Quantity: `10`
   - Unit: `ream`
   - Est. Unit Price: `150.00`
   - Confirm subtotal shows ZMW 1,500.00
7. Click **Add Another Item**, add Line Item 2:
   - Description: `USB Drive 32GB`
   - Category: `IT`
   - Quantity: `5`
   - Unit: `pcs`
   - Est. Unit Price: `200.00`
   - Confirm subtotal shows ZMW 1,000.00
8. Confirm Estimated Total shows ZMW 2,500.00.
9. Click **Submit Requisition**.
10. Confirm redirect to **My Requisitions** list.
11. Confirm the new PR appears with status badge **Pending Hod** (amber).
12. Note the reference number (e.g. `PR-0001`).

**Validation checks (repeat with bad data):**
- Submit with no items → expect validation error on description/quantity/price.
- Submit with price = 0 → expect validation error.

---

## Section 3 — HOD Approval

**Login:** `hod@binarypivot.test`

1. Click **All Requisitions** in the Approvals section.
2. Confirm the PR from Section 2 appears with status **Pending Hod**.
3. Click the PR to open it (`/requisitions/{id}`).
4. Confirm the right panel shows **Your Action Required** with Approve / Reject buttons.
5. Click **Approve**.
6. The confirmation modal opens. Add an optional comment: `Approved for IT supplies.`
7. Click **Confirm**.
8. Confirm the status badge changes to **Pending Stores** (orange).
9. Confirm a new entry appears in the Approval Trail: "Approved — Hod" by Bob HOD.

**Rejection path (use a second PR):**
> Create another PR as Requester first, then come back as HOD.
1. Click **Reject**.
2. Modal opens — attempt to confirm **without** a comment → expect an error (comment required).
3. Enter a comment: `Budget not aligned. Please revise.`
4. Click **Confirm**.
5. Status changes to **Rejected** (red).
6. Approval Trail shows "Rejected — Hod" with the comment.

---

## Section 4 — Stores Check

**Login:** `stores@binarypivot.test`

### 4A — Issue from Stores (items in stock)

1. Click **All Requisitions** → open the PR with status **Pending Stores**.
2. Right panel shows **Issue from Stores** and **Not in Stock** buttons.
3. Click **Issue from Stores**.
4. Add comment: `All items available in stores.`
5. Click **Confirm**.
6. Status changes to **Issued From Stores** (cyan) — this is a terminal happy path.
7. Approval Trail shows "Issued — Stores" by Carol Stores.

### 4B — Not in Stock (items must be procured)

> Use a separate PR that is in **Pending Stores** state for this path.

1. Open a **Pending Stores** PR.
2. Click **Not in Stock**.
3. Add comment: `Items not stocked. Sending to procurement route.`
4. Click **Confirm**.
5. Status changes to **Pending Bursar** (yellow).

---

## Section 5 — Bursar: Fund Commitment

**Login:** `bursar@binarypivot.test`

1. Open the PR with status **Pending Bursar**.
2. Right panel shows the estimated total and **Commit Funds / Decline** buttons.
3. Click **Commit Funds**.
4. Add optional comment: `Funds available in Q1 budget.`
5. Click **Confirm**.
6. Status changes to **Pending Vc Requisition** (blue).
7. Approval Trail shows "Committed — Bursar".

**Rejection path:**
1. Open a separate **Pending Bursar** PR (create another one if needed).
2. Click **Decline**.
3. Attempt confirm without comment → expect error.
4. Add comment: `Insufficient funds in this cost centre.`
5. Click **Confirm** → status changes to **Rejected**.

---

## Section 6 — VC Requisition Approval

**Login:** `vc@binarypivot.test`

1. Open the PR with status **Pending Vc Requisition**.
2. Right panel shows **Approve / Reject** buttons.
3. Click **Approve**.
4. Add optional comment: `Approved by VC.`
5. Click **Confirm**.
6. Status changes to **Pending Procurement** (violet).
7. Approval Trail shows "Approved — Vc Requisition".

**Rejection path:**
1. Click **Reject** on a separate PR in the same state.
2. Comment required — add: `Requisition needs further justification.`
3. Confirm → status changes to **Rejected**.

---

## Section 7 — Procurement: Quotes & Submit for Audit

**Login:** `procurement@binarypivot.test`

### 7.1 Add Supplier Quotes

1. Open the PR with status **Pending Procurement**.
2. Right panel shows **Add Supplier Quote** and **Submit for Audit** buttons.
3. Click **Add Supplier Quote**.
4. Quote modal opens. Select supplier: `TechZambia Ltd`.
5. Enter Amount: `2,450.00`, Received Date: today, Notes: `Best price guarantee`.
6. Click **Save Quote**.
7. Confirm the quote appears in the Supplier Quotes section.
8. Add a second quote: `Office Depot Lusaka`, Amount: `2,800.00`.
9. Confirm both quotes are listed. The lowest (TechZambia) should be highlighted.

### 7.2 Submit for Audit

1. Click **Submit for Audit**.
2. Status changes to **Pending Audit** (purple).
3. Approval Trail shows "Approved — Procurement" (the submit action).

### 7.3 Create Purchase Order (while pending audit)

> Procurement can draft the PO while audit runs in parallel.

1. The right panel now shows a **Draft Purchase Order** card (violet).
2. Click **Create Purchase Order** → navigates to `/requisitions/{id}/po/create`.
3. Select Awarded Supplier: `TechZambia Ltd` (the lowest quote).
4. Set Expected Delivery Date: 2 weeks from today.
5. Review the pre-filled line items. Adjust unit prices to match the quote (ZMW 245.00 per item if needed).
6. Confirm PO Total calculates correctly.
7. Click **Create Purchase Order**.
8. Confirm redirect back to the PR detail page.
9. A **Purchase Order** section now appears with the PO number (e.g. `PO-0001`).
10. Click the PO number link → opens `/purchase-orders/{id}`.

---

## Section 8 — Audit Review

**Login:** `auditor@binarypivot.test`

1. Open the PR with status **Pending Audit**.
2. Right panel shows **Note (Pass)** and **Reject** buttons.
3. Click **Note (Pass)**.
4. Add optional comment: `All compliance checks passed.`
5. Click **Confirm**.
6. Status changes to **Pending Vc Payment** (indigo).
7. Approval Trail shows "Noted — Audit".

**Rejection path:**
1. Click **Reject** on a separate **Pending Audit** PR.
2. Comment: `Quote comparison missing from attachments.`
3. Status changes to **Rejected**.

> Note: If automated compliance checks fail, the system will block a "Note" decision and display the specific failure messages. Resolve any flagged issues before passing.

---

## Section 9 — VC Payment Approval

**Login:** `vc@binarypivot.test`

1. Open the PR with status **Pending Vc Payment**.
2. Right panel shows **Approve Payment / Suspend** buttons.
3. Click **Approve Payment**.
4. Add optional comment: `Payment authorised.`
5. Click **Confirm**.
6. Status changes to **Pending Payment** (emerald).
7. Approval Trail shows "Approved — Vc Payment".

**Suspension path:**
1. Click **Suspend** on a separate PR.
2. Comment required: `Payment suspended pending supplier verification.`
3. Status changes to **Suspended** (rose).

---

## Section 10 — Bursar: Process Payment

**Login:** `bursar@binarypivot.test`

1. Open the PR with status **Pending Payment**.
2. Right panel shows **Mark as Paid / Flag Delay** buttons.
3. Click **Mark as Paid**.
4. Add optional comment: `EFT transfer ref: ZMW-2024-001.`
5. Click **Confirm**.
6. Status changes to **Paid** (green).
7. Approval Trail shows "Paid — Bursar" (or similar final entry).

---

## Section 11 — Attachment Upload

**Tested as:** Requester or Procurement (any non-terminal PR)

1. Open any active PR (not Rejected/Suspended/Delivered).
2. In the Attachments section, click **Upload Attachment** (or the upload action button).
3. Upload a small PDF or image file.
4. Select type: `Memo` (or Quote Evidence / Specification).
5. Confirm the file appears in the list with: filename, type, size in KB, uploader name, date.
6. Click **Download** → file downloads correctly.
7. If logged in as the uploader or admin, a **Delete** button appears — click it and confirm the wire:confirm dialog. File is removed.

---

## Section 12 — Purchase Orders List

**Login:** `procurement@binarypivot.test` or `stores@binarypivot.test`

1. Click **Purchase Orders** in the sidebar.
2. URL: `/purchase-orders`.
3. Confirm the PO created in Section 7.3 appears.
4. Click the PO to open `/purchase-orders/{id}`.
5. Confirm PO details show: PO number, supplier, line items, total value, status, linked PR reference.

---

## Section 13 — Reports

**Login:** `bursar@binarypivot.test` (or VC / Auditor / Procurement / Admin)

1. Click **Reports** in the sidebar.
2. URL: `/reports`.
3. Confirm the page loads with charts or summary data.
4. Click **Export PDF** → a PDF downloads (`/reports/export/pdf`).
5. Click **Export Excel** → a spreadsheet downloads (`/reports/export/excel`).
6. Confirm both exports contain data for the PRs created during this test run.

---

## Section 14 — HOD Delegation

**Login:** `admin@binarypivot.test`

1. Navigate to Admin > HOD Delegations (`/admin/hod-delegations`).
2. Click **New Delegation**.
3. Select Department: IT.
4. Delegate to: `Alice Requester` (or any other user in the dropdown).
5. Set dates: today → 7 days from today.
6. Reason: `Annual leave cover`.
7. Click **Create Delegation**.
8. Confirm the delegation appears in the table as active.

**Test the delegation works:**
1. Create a new PR as `requester@binarypivot.test` and submit it (status → Pending Hod).
2. Log in as `requester@binarypivot.test` (the delegate).
3. Open **All Requisitions** → find the new PR.
4. The right panel shows **Your Action Required** (HOD approve/reject buttons).
5. Approve the PR.
6. Open the Approval Trail → confirm entry shows "Approved — Hod" with the delegate's name (and the original HOD noted as the delegating authority).

**Revocation:** Create a second delegation for the same department — the system should auto-revoke the first one. Confirm only one active delegation exists.

---

## Section 15 — Access Control Checks

Verify that roles cannot access pages they shouldn't.

| Login as | Try to visit | Expected result |
|---|---|---|
| Requester | `/admin/users` | Redirect or 403 |
| Requester | `/purchase-orders` | Redirect or 403 |
| Stores | `/reports` | Redirect or 403 |
| HOD | `/admin/users` | Redirect or 403 |
| Procurement | `/admin/users` | Redirect or 403 |

---

## Section 16 — Dark Mode

1. Logged in as any user, toggle dark mode using the theme switcher in the header.
2. Confirm the sidebar, cards, tables, and modals all switch to dark styles correctly.
3. Refresh the page — confirm dark mode persists.

---

## Full Workflow Summary

```
Requester submits PR
      ↓
HOD approves          ← HOD can also reject (terminal)
      ↓
Stores checks stock
  ├── Issue from Stores  (terminal happy path — items in stock)
  └── Not in Stock
        ↓
      Bursar commits funds     ← can reject (terminal)
        ↓
      VC approves requisition  ← can reject (terminal)
        ↓
      Procurement adds quotes + creates PO (parallel)
        ↓
      Audit reviews compliance ← can reject (terminal)
        ↓
      VC approves payment      ← can suspend (terminal)
        ↓
      Bursar marks paid
        ↓
      Delivered
```

---

## Checklist Summary

- [ ] Admin pages all load without errors
- [ ] Suppliers and stock items added via UI
- [ ] Budget allocation created
- [ ] Sidebar items match each role's permissions
- [ ] PR created and submitted successfully
- [ ] HOD approval moves PR to Pending Stores
- [ ] HOD rejection requires comment, moves to Rejected
- [ ] Stores: Issue from Stores → Issued From Stores
- [ ] Stores: Not in Stock → Pending Bursar
- [ ] Bursar: Commit Funds → Pending VC Requisition
- [ ] Bursar: Decline requires comment → Rejected
- [ ] VC: Approve Requisition → Pending Procurement
- [ ] Procurement: quotes added, Submit for Audit → Pending Audit
- [ ] PO created during Pending Audit stage
- [ ] Auditor: Note (Pass) → Pending VC Payment
- [ ] VC: Approve Payment → Pending Payment
- [ ] VC: Suspend requires comment → Suspended
- [ ] Bursar: Mark as Paid → Paid
- [ ] Attachment upload, download, and delete work
- [ ] Purchase Orders list and detail load correctly
- [ ] Reports page loads and exports (PDF + Excel) work
- [ ] HOD Delegation created and tested with delegate approval
- [ ] Access control blocks wrong roles from restricted pages
- [ ] Dark mode toggles and persists
