import React, { useState, useEffect } from "react";
import CreatableSelect from "react-select/creatable";
import axios from "axios";
import Swal from "sweetalert2";

const CustomerSelect = ({ setCustomerId }) => {
  const [customers, setCustomers] = useState([]);
  const [selectedCustomer, setSelectedCustomer] = useState(null);

  // Fetch existing customers
  useEffect(() => {
    axios
      .get("/admin/get/customers")
      .then((res) => {
        const options = res.data.map((c) => ({
          value: c.id,
          label: `${c.name}${c.phone ? ` (${c.phone})` : ""}${c.address ? ` - ${c.address}` : ""}`,
        }));
        setCustomers(options);

        const walking = options.find((c) => c.label.includes("Walking Customer"));
        if (walking) setSelectedCustomer(walking);
      })
      .catch((err) => console.error(err));
  }, []);

  // Update POS with selected customer ID
  useEffect(() => {
    if (selectedCustomer) setCustomerId(selectedCustomer.value);
  }, [selectedCustomer]);

  // Create new customer
  const handleCreateCustomer = async (inputValue) => {
    const { value: formValues } = await Swal.fire({
      title: "Create Customer",
      html:
        `<input id="swal-name" class="swal2-input" placeholder="Name" value="${inputValue}" style="margin:3px;padding:6px;">` +
        `<input id="swal-phone" class="swal2-input" placeholder="Phone (optional)" style="margin:3px;padding:6px;">` +
        `<input id="swal-address" class="swal2-input" placeholder="Address (optional)" style="margin:3px;padding:6px;">`,
      showCancelButton: true,
      confirmButtonText: "Save",
      cancelButtonText: "Cancel",
      focusConfirm: false,
      didOpen: () => {
        document.getElementById("swal-name").focus();
        ["swal-name", "swal-phone", "swal-address"].forEach((id) => {
          document.getElementById(id).addEventListener("keydown", (e) => {
            if (e.key === "Enter") Swal.clickConfirm();
          });
        });
      },
      preConfirm: () => ({
        name: document.getElementById("swal-name").value.trim(),
        phone: document.getElementById("swal-phone").value.trim() || null,
        address: document.getElementById("swal-address").value.trim() || null,
      }),
    });

    if (!formValues || !formValues.name) return;

    try {
      const res = await axios.post("/admin/create/customers", formValues);
      const newCustomer = res.data;

      const newOption = {
        value: newCustomer.id,
        label: `${newCustomer.name}${newCustomer.phone ? ` (${newCustomer.phone})` : ""}${newCustomer.address ? ` - ${newCustomer.address}` : ""}`,
      };

      setCustomers((prev) => [newOption, ...prev]);
      setSelectedCustomer(newOption);
    } catch (err) {
      console.error(err);
      Swal.fire("Error", err.response?.data?.message || "Failed to create customer", "error");
    }
  };

  return (
    <CreatableSelect
      isClearable
      options={customers}
      onChange={(val) => setSelectedCustomer(val)}
      onCreateOption={handleCreateCustomer}
      value={selectedCustomer}
      placeholder="Select or create customer"
    />
  );
};

export default CustomerSelect;
