// This is a dedicated file to handle every dropdowns in the app

export const dropdownProcessor = {
  dropdowns: {
    appMenuDropdown: false,
  },
  checkAppClick: function (event)
  {
    // Check if menu exists AND click was outside
    const menu = this.$refs.appMenuDropdownRef;
    if (menu && !menu.contains(event.target)) {
        this.dropdowns.appMenuDropdown = false;
    }
  }
};