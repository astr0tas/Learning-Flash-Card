document.addEventListener('alpine:init', () =>
{
  Alpine.data('cardBag', () => ({
    normalInputClass: "w-full rounded-lg px-3.5 py-3 outline-none focus:ring-2 focus:ring-offset-0 transition-all peer select-none border border-gray-400 focus:ring-blue-200 focus:ring-offset-white focus:border-blue-500 !text-base",
    openNewBagModal() {
      document.getElementById('addNewBagModal').open = true;

      document.getElementById('new_bag_name_error')?.remove();
      document.getElementById('new_bag_name').className = this.normalInputClass;

      document.getElementById('new_bag_name').value = '';
      document.getElementById('new_bag_description').value = '';
    },
  }));
});