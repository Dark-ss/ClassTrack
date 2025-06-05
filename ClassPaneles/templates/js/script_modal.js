const modal = document.getElementById('createUserModal');
        const openBtn = document.getElementById('openCreateUserModal');
        const cancelBtn = document.querySelector('.cancel-button');
        const uploadBtn = document.getElementById('uploadPhotoBtn');
        const photoInput = document.getElementById('photoInput');
        const profileImage = document.getElementById('profileImage');

        openBtn.onclick = () => (modal.style.display = 'flex');
        cancelBtn.onclick = () => (modal.style.display = 'none');

        window.onclick = (event) => {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        };

        uploadBtn.onclick = (e) => {
            e.preventDefault();
            photoInput.click();
        };

        photoInput.onchange = (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    profileImage.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        };
