// This is a dedicated file to handle every dropdowns in the app

export const dropdownProcessor = {
  dropdowns: {
    appMenuDropdown: false,
    addDropdown: false,
    optionDropdown: false,
  },
  checkAppClick: function (event)
  {
    // Check if menu exists AND click was outside
    const menu = this.$refs.appMenuDropdownRef;
    if (menu && !menu.contains(event.target)) {
        this.dropdowns.appMenuDropdown = false;
    }

    // Check if add dropdown exists AND click was outside
    const addDropdownElem = this.$refs.addDropdownRef;
    if (addDropdownElem && !addDropdownElem.contains(event.target)) {
        this.dropdowns.addDropdown = false;
    }

    // Check if option dropdown exists AND click was outside
    const optionDropdownElem = this.$refs.optionDropdownRef;
    if (optionDropdownElem && !optionDropdownElem.contains(event.target)) {
        this.dropdowns.optionDropdown = false;
    }
  }
};