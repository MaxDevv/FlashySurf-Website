function getUTMParams() {
  const params = new URLSearchParams(window.location.search);

  return {
    source: params.get("utm_source"),
    campaign: params.get("utm_campaign"),
  };
}

document.addEventListener('DOMContentLoaded', function() {
    // Near entry of your product, init Mixpanel
mixpanel.init("22a8b73e3d3e331db77e3ea39db149be", {
    debug: false,
    track_pageview: false,
    persistence: "localStorage",
});
console.log("Mixpanel global is:", mixpanel);

    mixpanel.track('Visit Website', {
        ...getUTMParams()

    });
    document.querySelectorAll('a[href*="chromewebstore.google.com"]').forEach((e) => {
        e.addEventListener('click', () => {

            mixpanel.track('Visit Extension Page', {
                ...getUTMParams()
            });
        })
    })
});