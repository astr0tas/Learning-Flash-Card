document.addEventListener('alpine:init', () =>
{
  Alpine.data('cardBag', () => ({
    normalInputClass: "w-full rounded-lg px-3.5 py-3 outline-none focus:ring-2 focus:ring-offset-0 transition-all peer select-none border border-gray-400 focus:ring-blue-200 focus:ring-offset-white focus:border-blue-500 !text-base",
    filteredBagList: JSON.parse(JSON.stringify(bagList)),
    filteredCardList: JSON.parse(JSON.stringify(cardList)),
    selectCard: '',
    selectedBags: [],
    selectedCards: [],
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
    init()
    {
      this.$watch('selectedBags', () => {
        this.$dispatch('update-select-bag', this.selectedBags);
      });
      this.$watch('selectedCards', () => {
        this.$dispatch('update-select-card', this.selectedCards);
      });
    }
  }));

  Alpine.data('objectMove', () => ({
    selectedBags: [],
    selectedCards: [],
    originalParent: objectMovingBreadcrumb.length > 0 ? objectMovingBreadcrumb[objectMovingBreadcrumb.length - 1].id : null,
    newParentBag: objectMovingBreadcrumb.length > 0 ? objectMovingBreadcrumb[objectMovingBreadcrumb.length - 1].id : null,
    objectMovingBreadcrumb,
    parentBagContent: [],
    filteredParentBagContent: [],
    searchFilter: '',
    isLoading: false,
    async fetchParentBagContent()
    {
      this.toggleLoading();

      const params = { parentBagId: this.newParentBag };
      const queryString = new URLSearchParams(params).toString();
      const url = `${ fetchBagContentUrl }?${ queryString }`;

      await fetch(url)
        .then(response => response.json())
        .then(data =>
        {
          this.parentBagContent = data.filter(elem => !this.selectedBags.some(bag => bag == elem.id));
          this.applySearch();
          document.getElementById('objectMovingBreadcrumb').dispatchEvent(new CustomEvent('set-breadcrumb-items', { detail: this.objectMovingBreadcrumb }));
        })
        .catch(error =>
        {
          console.error("Error when fetching bag list: ", error);
          this.pushNotification(apiRequestError, 'error');
        })

      this.toggleLoading();
    },
    toggleLoading()
    {
      this.isLoading = !this.isLoading;

      if (this.isLoading)
      {
        document.getElementById('bag-content-list-loading').dispatchEvent(new CustomEvent('set-loading'));
      } else
      {
        document.getElementById('bag-content-list-loading').dispatchEvent(new CustomEvent('unset-loading'));
      }
    },
    applySearch()
    {
      if (!this.searchFilter)
      {
        this.filteredParentBagContent = JSON.parse(JSON.stringify(this.parentBagContent));
        return;
      }

      const normalizedSearch = this.removeDiacritics(this.searchFilter.toLowerCase());
      const searchKeywords = normalizedSearch.split(/[~`!@#$%^&*()_+\-\=\[\]{}\\|;':"<>,./? ]+/).filter(keyword => keyword);

      this.filteredParentBagContent = this.parentBagContent.filter(bag => {
        const normalizedBagName = this.removeDiacritics(bag.name.toLowerCase());
        return searchKeywords.some(keyword => normalizedBagName.includes(keyword));
      });
    },
    clickConfirm()
    {
      document.getElementById('moveObjectModal').removeAttribute('open');
      this.$refs.moveObjectFormRef.requestSubmit();
    },
    resetMoveObjectModal()
    {
      this.newParentBag = this.originalParent;
    },
    init()
    {
      this.$watch('newParentBag', (value) =>
      {
        const findIndex = this.objectMovingBreadcrumb.findIndex(elem => elem.id === value);

        if (findIndex !== -1)
        {
          this.objectMovingBreadcrumb = this.objectMovingBreadcrumb.slice(0, findIndex + 1);
        } else
        {
          const result = this.parentBagContent.find(elem => elem.id === value);
          this.objectMovingBreadcrumb.push({
            id: result.id,
            label: result.name,
            action: `document.getElementById('moveObjectModal').dispatchEvent(new CustomEvent('set-new-parent-bag', { detail: ${result.id} }))`
          });
        }

        this.fetchParentBagContent();
      });

      this.$watch('searchFilter', () =>
      {
        this.applySearch();
      });

      this.$watch('selectedBags', () =>
      {
        this.fetchParentBagContent();
      });

      this.fetchParentBagContent();
    }
  }));
});