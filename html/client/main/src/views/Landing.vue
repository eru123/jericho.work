<template>
  <v-public-header />
  <div class="landing-1" v-once>
    <h3>Your dream and solution<span>let's make it!</span></h3>
    <p>
      We work closely with you to understand your unique needs and develop
      customized solutions that turn your dreams into reality. Our commitment to
      collaboration and partnership ensures that we work together with you every
      step of the way to make your vision a reality.
    </p>

    <a href="#info">Learn More</a>
  </div>
  <div class="news-letter">
    <div class="container">
      <div class="news-letter-card">
        <div class="news-letter-title">
          <h2>Subscribe to our newsletter</h2>
        </div>
        <div class="news-letter-form">
          <form action="#">
            <input type="text" placeholder="Enter your email address" />
            <button type="submit">Subscribe</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <v-public-footer />
</template>
<style scoped lang="scss">
.landing-1 {
  @apply w-full max-w-screen-md mx-auto min-h-[calc(100vh-3.5rem)] flex flex-col justify-center items-center text-center px-4 py-8;

  h3 {
    @apply text-2xl font-semibold text-primary-900 mb-6;

    span {
      @apply mt-2 block uppercase text-primary-800 text-4xl font-bold;
    }
  }

  p {
    @apply text-lg text-primary-900 mx-auto max-w-prose mb-4;
  }

  a {
    @apply px-4 py-2 rounded-md text-sm font-normal text-primary-50 bg-primary-900 hover:bg-primary-800;
  }
}

.landing-2 {
  @apply w-full max-w-screen-md mx-auto min-h-[calc(100vh-3.5rem)] flex flex-col justify-center items-center text-center px-4 py-8;

  &:target {
    @apply mt-[-3.5rem];
  }
}

.news-letter {
  @apply w-full pb-8 bg-white md:pb-12 px-4 md:px-4;

  .container {
    @apply w-full max-w-screen-sm mx-auto flex flex-col justify-center items-center text-center;

    .news-letter-card {
      @apply w-full bg-white rounded-md shadow-md overflow-hidden;

      .news-letter-title {
        @apply w-full bg-primary-900 text-white py-4 px-4;

        h2 {
          @apply text-2xl font-semibold;
        }
      }

      .news-letter-form {
        @apply w-full bg-white py-8 px-4;

        form {
          @apply flex flex-col md:flex-row md:justify-center md:items-center m-0;

          input {
            @apply w-full text-base md:text-sm md:w-96 px-4 py-2 border border-gray-300 rounded-md mb-4 md:mb-0 md:mr-4;
          }

          button {
            @apply px-4 py-2 rounded-md text-base md:text-sm font-normal text-primary-50 bg-primary-900 hover:bg-primary-800;
          }
        }
      }
    }
  }
}
</style>
