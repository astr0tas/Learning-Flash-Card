document.addEventListener('alpine:init', () =>
{
  Alpine.data('cardBag', () => ({
    normalInputClass: "w-full rounded-lg px-3.5 py-3 outline-none focus:ring-2 focus:ring-offset-0 transition-all peer select-none border border-gray-400 focus:ring-blue-200 focus:ring-offset-white focus:border-blue-500 !text-base",
    filteredBagList: JSON.parse(JSON.stringify(bagList)),
    filteredCardList: JSON.parse(JSON.stringify(cardList)),
    selectCard: '',
    selectedBags: [],
    selectedCards: [],
    objectMovingBreadcrumb: [],
    newParentBag: null,
    parentBagContent: [],
    openCardDetailModal(id)
    {
      const card = this.filteredCardList.find(c => c.id == id);
      this.$dispatch('set-view-card-data', card);
      document.getElementById('cardDetailModal').setAttribute('open',true);
    },
    openEditCardModal(id)
    {
      const card = this.filteredCardList.find(c => c.id == id);
      this.$dispatch('set-edit-card-inputs', card);
      document.getElementById('editCardModal').setAttribute('open',true);
      this.$dispatch('close-view-card-modal');
    },
    filterBagAndCard(search)
    {
      if (!search)
      {
        this.filteredBagList = JSON.parse(JSON.stringify(bagList));
        this.filteredCardList = JSON.parse(JSON.stringify(cardList));
        return;
      }

      const normalizedSearch = this.removeDiacritics(search.toLowerCase());
      const searchKeywords = normalizedSearch.split(/[~`!@#$%^&*()_+\-\=\[\]{}\\|;':"<>,./? ]+/).filter(keyword => keyword);

      this.filteredBagList = bagList.filter(bag => {
        const normalizedBagName = this.removeDiacritics(bag.name.toLowerCase());
        return searchKeywords.some(keyword => normalizedBagName.includes(keyword));
      });

      this.filteredCardList = cardList.filter(card => {
        const normalizedCardTitle = this.removeDiacritics(card.title.toLowerCase());
        return searchKeywords.some(keyword => normalizedCardTitle.includes(keyword));
      });
    },
    fetchParentBagContent(id)
    {
      const params = { parentBagId: id };
      const queryString = new URLSearchParams(params).toString();
      const url = `${ fetchBagContentUrl }?${ queryString }`;

      fetch(url)
        .then(response => response.json())
        .then(data =>
        {
        })
        .catch(error =>
        {
          console.error("Error when fetching bag list: ", error);
          this.pushNotification(apiRequestError,'error');
        })
    },
    init()
    {
      this.$watch('selectedBags', () =>{
        this.$dispatch('update-select-bag', this.selectedBags);
      });
      this.$watch('selectedCards', () =>{
        this.$dispatch('update-select-card', this.selectedCards);
      });
      this.$watch('newParentBag', (value) => { this.fetchParentBagContent(value) });
    }
  }));
});