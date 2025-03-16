/**
 * Inventory Management System
 * 
 * This script handles inventory tracking, filtering, and display functions
 */

// Sample inventory data structure
let inventoryItems = [
    {
      name: "Item 1",
      type: "Type A",
      serialNo: "SN001",
      expiry: "2025-06-15",
      quantity: 75,
    },
    {
      name: "Item 2",
      type: "Type B",
      serialNo: "SN002",
      expiry: "2025-08-22",
      quantity: 25,
    },
    {
      name: "Item 3",
      type: "Type A",
      serialNo: "SN003",
      expiry: "2025-07-30",
      quantity: 55,
    },
  ];
  
  /**
   * Initialize the inventory system
   */
  function initInventory() {
    loadInventoryData();
    setupEventListeners();
    renderInventoryTable();
    updateInventoryVisuals();
  }
  
  /**
   * Load inventory data from storage or API
   */
  function loadInventoryData() {
    // In a real application, this would fetch data from localStorage, IndexedDB, or a server API
    const storedData = localStorage.getItem('inventoryData');
    if (storedData) {
      try {
        inventoryItems = JSON.parse(storedData);
      } catch (e) {
        console.error('Failed to parse stored inventory data', e);
      }
    }
  }
  
  /**
   * Save inventory data to storage
   */
  function saveInventoryData() {
    localStorage.setItem('inventoryData', JSON.stringify(inventoryItems));
  }
  
  /**
   * Set up event listeners for the inventory interface
   */
  function setupEventListeners() {
    // Search/Filter button
    const searchButton = document.getElementById('search-button') || document.querySelector('button:contains("Go")');
    if (searchButton) {
      searchButton.addEventListener('click', filterInventory);
    }
  
    // Input field listeners for real-time filtering (optional)
    const filterInputs = document.querySelectorAll('.filter-input, input[type="text"]');
    filterInputs.forEach(input => {
      input.addEventListener('input', debounce(filterInventory, 300));
    });
  }
  
  /**
   * Filter inventory based on search criteria
   */
  function filterInventory() {
    const nameFilter = (document.getElementById('name-filter') || document.querySelector('input[placeholder*="Name"]'))?.value?.toLowerCase();
    const typeFilter = (document.getElementById('type-filter') || document.querySelector('input[placeholder*="Type"]'))?.value?.toLowerCase();
    const serialFilter = (document.getElementById('serial-filter') || document.querySelector('input[placeholder*="Serial"]'))?.value?.toLowerCase();
    const expiryFilter = (document.getElementById('expiry-filter') || document.querySelector('input[placeholder*="Expiry"]'))?.value;
    const quantityFilter = (document.getElementById('quantity-filter') || document.querySelector('input[placeholder*="Quantity"]'))?.value;
  
    const filteredItems = inventoryItems.filter(item => {
      const matchesName = !nameFilter || item.name.toLowerCase().includes(nameFilter);
      const matchesType = !typeFilter || item.type.toLowerCase().includes(typeFilter);
      const matchesSerial = !serialFilter || item.serialNo.toLowerCase().includes(serialFilter);
      const matchesExpiry = !expiryFilter || item.expiry.includes(expiryFilter);
      const matchesQuantity = !quantityFilter || item.quantity.toString().includes(quantityFilter);
  
      return matchesName && matchesType && matchesSerial && matchesExpiry && matchesQuantity;
    });
  
    renderInventoryTable(filteredItems);
    updateInventoryVisuals(filteredItems);
  }
  
  /**
   * Render the inventory table with the provided data
   * @param {Array} items - Inventory items to display (defaults to all items)
   */
  function renderInventoryTable(items = inventoryItems) {
    const tableBody = document.querySelector('tbody') || document.querySelector('.inventory-table');
    if (!tableBody) return;
  
    // Clear existing rows
    while (tableBody.firstChild) {
      tableBody.removeChild(tableBody.firstChild);
    }
  
    // Add rows for each item
    items.forEach(item => {
      const row = document.createElement('tr');
      
      // Add cells for each property
      ['name', 'type', 'serialNo', 'expiry', 'quantity'].forEach(prop => {
        const cell = document.createElement('td');
        cell.textContent = item[prop];
        row.appendChild(cell);
      });
  
      // Add action buttons if needed
      const actionsCell = document.createElement('td');
      const editButton = document.createElement('button');
      editButton.textContent = 'Edit';
      editButton.onclick = () => editItem(item.serialNo);
      actionsCell.appendChild(editButton);
      row.appendChild(actionsCell);
  
      tableBody.appendChild(row);
    });
  }
  
  /**
   * Update visual indicators (charts/graphs) based on inventory data
   * @param {Array} items - Inventory items to visualize
   */
  function updateInventoryVisuals(items = inventoryItems) {
    // Update the pie charts or other visuals based on data
    updateQuantityChart(items);
    updateExpiryChart(items);
    updateTypeDistributionChart(items);
  }
  
  /**
   * Update the quantity chart
   * @param {Array} items - Items to visualize
   */
  function updateQuantityChart(items) {
    // In a real application, this would use a charting library like Chart.js
    // For now, we'll just log the total quantity
    const totalQuantity = items.reduce((sum, item) => sum + item.quantity, 0);
    console.log('Total quantity:', totalQuantity);
    
    // Update the visual representation
    const quantityChart = document.querySelector('.quantity-chart');
    if (quantityChart) {
      // Update chart logic would go here
    }
  }
  
  /**
   * Update the expiry chart
   * @param {Array} items - Items to visualize
   */
  function updateExpiryChart(items) {
    // Group items by expiry month/year
    const expiryGroups = items.reduce((groups, item) => {
      const expiry = new Date(item.expiry);
      const monthYear = `${expiry.getMonth() + 1}/${expiry.getFullYear()}`;
      
      if (!groups[monthYear]) {
        groups[monthYear] = 0;
      }
      groups[monthYear] += item.quantity;
      
      return groups;
    }, {});
    
    console.log('Expiry distribution:', expiryGroups);
    
    // Update the visual representation
    const expiryChart = document.querySelector('.expiry-chart');
    if (expiryChart) {
      // Update chart logic would go here
    }
  }
  
  /**
   * Update the type distribution chart
   * @param {Array} items - Items to visualize
   */
  function updateTypeDistributionChart(items) {
    // Group items by type
    const typeGroups = items.reduce((groups, item) => {
      if (!groups[item.type]) {
        groups[item.type] = 0;
      }
      groups[item.type] += item.quantity;
      
      return groups;
    }, {});
    
    console.log('Type distribution:', typeGroups);
    
    // Update the visual representation
    const typeChart = document.querySelector('.type-chart');
    if (typeChart) {
      // Update chart logic would go here
    }
  }
  
  /**
   * Add a new inventory item
   * @param {Object} item - The item to add
   */
  function addInventoryItem(item) {
    // Validate required fields
    if (!item.name || !item.serialNo) {
      console.error('Name and Serial Number are required fields');
      return false;
    }
    
    // Check for duplicate serial numbers
    if (inventoryItems.some(existing => existing.serialNo === item.serialNo)) {
      console.error('An item with this Serial Number already exists');
      return false;
    }
    
    // Add the item
    inventoryItems.push(item);
    saveInventoryData();
    renderInventoryTable();
    updateInventoryVisuals();
    return true;
  }
  
  /**
   * Update an existing inventory item
   * @param {string} serialNo - The serial number of the item to update
   * @param {Object} updatedData - The updated data
   */
  function updateInventoryItem(serialNo, updatedData) {
    const index = inventoryItems.findIndex(item => item.serialNo === serialNo);
    if (index === -1) {
      console.error('Item not found');
      return false;
    }
    
    // Update the item
    inventoryItems[index] = { ...inventoryItems[index], ...updatedData };
    saveInventoryData();
    renderInventoryTable();
    updateInventoryVisuals();
    return true;
  }
  
  /**
   * Remove an inventory item
   * @param {string} serialNo - The serial number of the item to remove
   */
  function removeInventoryItem(serialNo) {
    const index = inventoryItems.findIndex(item => item.serialNo === serialNo);
    if (index === -1) {
      console.error('Item not found');
      return false;
    }
    
    // Remove the item
    inventoryItems.splice(index, 1);
    saveInventoryData();
    renderInventoryTable();
    updateInventoryVisuals();
    return true;
  }
  
  /**
   * Open the edit dialog for an item
   * @param {string} serialNo - The serial number of the item to edit
   */
  function editItem(serialNo) {
    const item = inventoryItems.find(item => item.serialNo === serialNo);
    if (!item) {
      console.error('Item not found');
      return;
    }
    
    // In a real application, this would open a modal dialog
    console.log('Editing item:', item);
    
    // Example of showing a form
    // showEditForm(item);
  }
  
  /**
   * Utility function to debounce frequent events like input
   * @param {Function} func - The function to debounce
   * @param {number} wait - The debounce wait time in milliseconds
   */
  function debounce(func, wait) {
    let timeout;
    return function(...args) {
      const context = this;
      clearTimeout(timeout);
      timeout = setTimeout(() => func.apply(context, args), wait);
    };
  }
  
  // Initialize the inventory system when the DOM is fully loaded
  document.addEventListener('DOMContentLoaded', initInventory);
  
  // Export functions for use in other modules
  if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
      addInventoryItem,
      updateInventoryItem,
      removeInventoryItem,
      filterInventory
    };
  }