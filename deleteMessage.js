setTimeout(() => {
            const alert = document.getElementsByTagName("h2")[0];
            if (alert) {
                alert.style.transition = "opacity 0.7s";
                alert.style.opacity = "0";
                setTimeout(() => {
                    alert.style.display = "none";
                }, 700);
            }
        }, 3000);