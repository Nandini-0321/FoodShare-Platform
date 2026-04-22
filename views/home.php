<section class="hero">
    <div class="hero-content">
        <h1>Welcome to FoodShare</h1>
        <p>Connecting food donors with those in need</p>
        <div class="cta-buttons">
            <a href="/FoodShare/?page=login" class="btn btn-primary">Login</a>
            <a href="/FoodShare/?page=register" class="btn btn-secondary">Register</a>
        </div>
    </div>
</section>

<style>
.hero {
    background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), 
                url('https://images.unsplash.com/photo-1504674900247-0877df9cc836?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
    background-size: cover;
    background-position: center;
    height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: white;
    border-radius: 10px;
}

.hero-content {
    max-width: 800px;
    padding: 2rem;
}

.hero h1 {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.hero p {
    font-size: 1.2rem;
    margin-bottom: 2rem;
}

.cta-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
}

.btn {
    padding: 0.8rem 1.5rem;
    border-radius: 5px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-primary {
    background-color: var(--primary);
    color: white;
}

.btn-secondary {
    background-color: transparent;
    border: 2px solid white;
    color: white;
}

.btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}
</style>
